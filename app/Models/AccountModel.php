<?php
declare(strict_types=1);
require_once __DIR__.'/UserModel.php';require_once __DIR__.'/OrderModel.php';
final class AccountModel
{
    public function __construct(private PDO $db){}
    public static function password(mixed $value,mixed $confirmation):string
    {
        if(!is_string($value) || !is_string($confirmation) || strlen($value)<12 || strlen($value)>72 || $value!==$confirmation || str_contains($value,"\0"))throw new InvalidArgumentException('Usa una contraseña de 12 a 72 bytes y repítela correctamente.');return $value;
    }
    private function fields(array $input):array
    {
        $fields=[];foreach(['nombres'=>100,'apellidos'=>100,'telefono'=>20] as $key=>$max){$value=$input[$key]??null;if(!is_string($value) || mb_strlen(trim($value))<1 || mb_strlen(trim($value))>$max)throw new InvalidArgumentException('Revisa nombres, apellidos y teléfono.');$fields[$key]=trim($value);}
        if(!preg_match('/^(?:\+?503)?[267][0-9]{7}$/D',preg_replace('/[ -]/','',$fields['telefono'])))throw new InvalidArgumentException('Introduce un teléfono válido de El Salvador.');return $fields;
    }
    private function lock(int $id):array
    {
        $q=$this->db->prepare("SELECT u.*,r.nombre_rol FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol WHERE u.id_usuario=? FOR UPDATE");$q->execute([$id]);$user=$q->fetch();if(!$user)throw new DomainException('Cuenta no encontrada.');return $user;
    }
    private function audit(int $user,?int $actor,string $action,string $detail):void
    {
        $q=$this->db->prepare('INSERT INTO tbcuentashistorial(id_usuario,id_actor,accion,detalle,created_at) VALUES (?,?,?,?,UTC_TIMESTAMP())');$q->execute([$user,$actor,$action,$detail]);
    }
    private static function revision(array $input,array $user):void
    {
        if(!is_string($input['revision']??null) || !ctype_digit($input['revision']) || (int)$input['revision']!==(int)$user['cuenta_revision'])throw new OrderConflict('La cuenta cambió. Actualiza los datos antes de guardar.');
    }
    public function profile(int $id,array $input):void
    {
        $fields=$this->fields($input);$this->db->beginTransaction();
        try{$user=$this->lock($id);self::revision($input,$user);if($user['estado']!=='activo' || !is_string($input['actual']??null) || !password_verify($input['actual'],$user['password_hash']))throw new DomainException('La contraseña actual no es correcta.');
            $q=$this->db->prepare('UPDATE tbusuarios SET nombres=?,apellidos=?,cuenta_revision=cuenta_revision+1 WHERE id_usuario=?');$q->execute([$fields['nombres'],$fields['apellidos'],$id]);
            $this->contact($id,$fields,$user['correo']);$this->audit($id,$id,'perfil','Datos personales actualizados.');$this->db->commit();
        }catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();throw $error;}
    }
    private function contact(int $id,array $fields,string $email):void
    {
        $q=$this->db->prepare('SELECT id_cliente FROM tbclientes WHERE id_usuario=? FOR UPDATE');$q->execute([$id]);$client=$q->fetchColumn();
        if($client){$q=$this->db->prepare('UPDATE tbclientes SET nombres=?,apellidos=?,telefono=? WHERE id_usuario=?');$q->execute([$fields['nombres'],$fields['apellidos'],$fields['telefono'],$id]);}
        else{$q=$this->db->prepare('INSERT INTO tbclientes(id_usuario,nombres,apellidos,telefono,correo_contacto) VALUES (?,?,?,?,?)');$q->execute([$id,$fields['nombres'],$fields['apellidos'],$fields['telefono'],$email]);}
    }
    private function replacePassword(array $user,string $password,?int $actor,string $action):void
    {
        $q=$this->db->prepare('UPDATE tbusuarios SET password_hash=?,session_version=session_version+1,cuenta_revision=cuenta_revision+1 WHERE id_usuario=?');$q->execute([password_hash($password,PASSWORD_DEFAULT),$user['id_usuario']]);
        $q=$this->db->prepare('UPDATE tbclaverecuperacion SET used_at=UTC_TIMESTAMP() WHERE id_usuario=? AND used_at IS NULL');$q->execute([$user['id_usuario']]);$this->audit((int)$user['id_usuario'],$actor,$action,'Contraseña actualizada; sesiones anteriores revocadas.');
    }
    public function changePassword(int $id,array $input):void
    {
        $password=self::password($input['nueva']??null,$input['confirmacion']??null);$this->db->beginTransaction();
        try{$user=$this->lock($id);if($user['estado']!=='activo' || !is_string($input['actual']??null) || !password_verify($input['actual'],$user['password_hash']))throw new DomainException('La contraseña actual no es correcta.');if(password_verify($password,$user['password_hash']))throw new InvalidArgumentException('Usa una contraseña diferente de la actual.');$this->replacePassword($user,$password,$id,'cambio_clave');$this->db->commit();}catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();throw $error;}
    }
    public function save(int $actor,?int $id,array $input):int
    {
        $fields=$this->fields($input);$role=$input['rol']??null;$status=$input['estado']??null;
        if(!in_array($role,['Administrador','Empleado','Cliente'],true) || !in_array($status,['activo','inactivo'],true) || !in_array($input['pedidos']??'0',['0','1'],true))throw new InvalidArgumentException('Rol, estado o permiso inválido.');
        $email=$input['correo']??'';$password=null;
        if($id===null){if(!is_string($email) || strlen($email)>150 || !filter_var($email,FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('Correo inválido.');$password=self::password($input['nueva']??null,$input['confirmacion']??null);}
        $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');$this->db->beginTransaction();
        try{
            // Serializa los cambios de administradores para proteger la última cuenta activa.
            $this->db->query("SELECT id_rol FROM tbroles WHERE nombre_rol='Administrador' FOR UPDATE")->fetchColumn();$admin=$this->lock($actor);if($admin['estado']!=='activo' || $admin['nombre_rol']!=='Administrador')throw new DomainException('Solo el administrador puede gestionar cuentas.');
            $q=$this->db->prepare('SELECT id_rol FROM tbroles WHERE nombre_rol=?');$q->execute([$role]);$roleId=(int)$q->fetchColumn();if(!$roleId)throw new DomainException('Rol no configurado.');
            if($id!==null){$user=$this->lock($id);self::revision($input,$user);
                if($id===$actor && ($role!=='Administrador' || $status!=='activo'))throw new DomainException('No puedes desactivar tu propia cuenta ni quitarte el rol de administrador.');
                if($user['nombre_rol']==='Administrador' && $user['estado']==='activo' && ($role!=='Administrador' || $status!=='activo') && (int)$this->db->query("SELECT COUNT(*) FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol WHERE r.nombre_rol='Administrador' AND u.estado='activo'")->fetchColumn()<=1)throw new DomainException('Debe quedar al menos un administrador activo.');
                $q=$this->db->prepare('UPDATE tbusuarios SET nombres=?,apellidos=?,id_rol=?,estado=?,session_version=session_version+?,cuenta_revision=cuenta_revision+1 WHERE id_usuario=?');$q->execute([$fields['nombres'],$fields['apellidos'],$roleId,$status,$status!==$user['estado']?1:0,$id]);$email=$user['correo'];
            }else{
                $username='usuario-'.bin2hex(random_bytes(12));$q=$this->db->prepare('INSERT INTO tbusuarios(id_rol,nombre_usuario,nombres,apellidos,correo,password_hash,estado) VALUES (?,?,?,?,?,?,?)');$q->execute([$roleId,$username,$fields['nombres'],$fields['apellidos'],trim($email),password_hash($password,PASSWORD_DEFAULT),$status]);$id=(int)$this->db->lastInsertId();
            }
            $this->contact($id,$fields,$email);$permission=$role==='Empleado' && ($input['pedidos']??'0')==='1'?1:0;
            $q=$this->db->prepare('INSERT INTO tbpersonalpermisos(id_usuario,pedidos,id_actor,updated_at) VALUES (?,?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE pedidos=VALUES(pedidos),id_actor=VALUES(id_actor),updated_at=UTC_TIMESTAMP()');$q->execute([$id,$permission,$actor]);
            $this->audit($id,$actor,'gestion_cuenta','Rol: '.$role.'; estado: '.$status.'; pedidos: '.($permission?'sí':'no').'.');$this->db->commit();return $id;
        }catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();if($error instanceof PDOException && (int)($error->errorInfo[1]??0)===1062)throw new InvalidArgumentException('Ya existe una cuenta con ese correo.');throw $error;}
    }
    public function requestReset(string $email):?array
    {
        $q=$this->db->prepare("SELECT id_usuario FROM tbusuarios WHERE correo=? AND estado='activo'");$q->execute([$email]);$id=(int)$q->fetchColumn();if(!$id)return null;
        $this->db->beginTransaction();
        try{$user=$this->lock($id);if($user['estado']!=='activo'){$this->db->commit();return null;}
            $q=$this->db->prepare('SELECT COUNT(*) FROM tbclaverecuperacion WHERE id_usuario=? AND created_at>DATE_SUB(UTC_TIMESTAMP(),INTERVAL 15 MINUTE)');$q->execute([$id]);if((int)$q->fetchColumn()>=3){$this->db->commit();return null;}
            $q=$this->db->prepare('UPDATE tbclaverecuperacion SET used_at=UTC_TIMESTAMP() WHERE id_usuario=? AND used_at IS NULL');$q->execute([$id]);$token=bin2hex(random_bytes(32));
            $q=$this->db->prepare('INSERT INTO tbclaverecuperacion(id_usuario,token_hash,session_version,expires_at,created_at) VALUES (?,?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 20 MINUTE),UTC_TIMESTAMP())');$q->execute([$id,hash('sha256',$token),$user['session_version']]);$reset=(int)$this->db->lastInsertId();$this->db->commit();return ['id'=>$reset,'token'=>$token,'correo'=>$user['correo'],'nombre'=>$user['nombres']];
        }catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();throw $error;}
    }
    public function reset(array $input):void
    {
        $token=$input['token']??null;$password=self::password($input['nueva']??null,$input['confirmacion']??null);if(!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D',$token))throw new DomainException('El enlace no es válido o ya venció.');
        $hash=hash('sha256',$token);$q=$this->db->prepare('SELECT id_usuario FROM tbclaverecuperacion WHERE token_hash=?');$q->execute([$hash]);$id=(int)$q->fetchColumn();if(!$id)throw new DomainException('El enlace no es válido o ya venció.');
        $this->db->beginTransaction();try{$user=$this->lock($id);$q=$this->db->prepare('SELECT * FROM tbclaverecuperacion WHERE token_hash=? AND used_at IS NULL AND expires_at>UTC_TIMESTAMP() FOR UPDATE');$q->execute([$hash]);$reset=$q->fetch();if(!$reset || $user['estado']!=='activo' || (int)$reset['session_version']!==(int)$user['session_version'])throw new DomainException('El enlace no es válido o ya venció.');$this->replacePassword($user,$password,null,'recuperacion_clave');$this->db->commit();}catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();throw $error;}
    }
}
