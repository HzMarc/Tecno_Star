<?php
declare(strict_types=1);
require_once BASE_PATH.'/core/Cash.php';
final class ReinvestmentModel
{
    public function __construct(private PDO $db){}
    public static function enabled(PDO $db):bool
    {
        static $connections=null;$connections??=new WeakMap();if(isset($connections[$db]))return $connections[$db];
        $current=(string)$db->query('SELECT CURRENT_USER()')->fetchColumn();$parts=explode('@',$current,2);$grantee="'".$parts[0]."'@'".($parts[1]??'')."'";
        $q=$db->prepare("SELECT COUNT(DISTINCT PRIVILEGE_TYPE) FROM information_schema.TABLE_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbreinversiones' AND PRIVILEGE_TYPE IN ('SELECT','INSERT')");$q->execute([$grantee]);
        // Las cuentas de migración también se utilizan en pruebas aisladas del modelo.
        $global=$db->prepare("SELECT COUNT(DISTINCT PRIVILEGE_TYPE) FROM information_schema.USER_PRIVILEGES WHERE GRANTEE=? AND PRIVILEGE_TYPE IN ('SELECT','INSERT')");$global->execute([$grantee]);
        return $connections[$db]=(int)$q->fetchColumn()===2 || (int)$global->fetchColumn()===2;
    }
    public function record(int $sale,string $total):void
    {
        if(!$this->db->inTransaction())throw new LogicException('La reinversión pertenece a la transacción de cobro.');
        Cash::cents($total);
        $q=$this->db->prepare('INSERT INTO tbreinversiones(id_venta,monto_venta,porcentaje_reposicion,porcentaje_crecimiento,created_at) VALUES (?,?,50,5,UTC_TIMESTAMP())');$q->execute([$sale,$total]);
    }
    public function forSale(int $sale):?array{$q=$this->db->prepare('SELECT * FROM tbreinversiones WHERE id_venta=?');$q->execute([$sale]);return $q->fetch()?:null;}
    public function data(array $input):array
    {
        foreach(['from','to','status','start','length','draw'] as $key)if(isset($input[$key]) && !is_string($input[$key]))throw new InvalidArgumentException('Parámetros inválidos.');
        if(isset($input['search']) && (!is_array($input['search']) || isset($input['search']['value']) && !is_string($input['search']['value'])))throw new InvalidArgumentException('Búsqueda inválida.');
        if(isset($input['order']) && !is_array($input['order']))throw new InvalidArgumentException('Orden inválido.');
        foreach($input['order']??[] as $item)if(!is_array($item) || !is_string($item['column']??null) || !ctype_digit($item['column']) || (int)$item['column']>8 || !in_array($item['dir']??null,['asc','desc'],true))throw new InvalidArgumentException('Orden inválido.');
        if(!empty($input['from']) && !empty($input['to']) && $input['from']>$input['to'])throw new InvalidArgumentException('El rango de fechas está invertido.');
        $where=[];$params=[];
        foreach(['from'=>'>=','to'=>'<'] as $key=>$operator){$value=$input[$key]??'';if($value==='')continue;if(!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$value))throw new InvalidArgumentException('Fecha inválida.');$date=DateTimeImmutable::createFromFormat('!Y-m-d',$value,new DateTimeZone('America/El_Salvador'));if(!$date || $date->format('Y-m-d')!==$value)throw new InvalidArgumentException('Fecha inválida.');if($key==='to')$date=$date->modify('+1 day');$where[]='r.created_at '.$operator.' ?';$params[]=$date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');}
        if(($input['status']??'')!==''){if(!in_array($input['status'],['pagada','anulada'],true))throw new InvalidArgumentException('Estado inválido.');$where[]='s.estado=?';$params[]=$input['status'];}
        $join=' FROM tbreinversiones r JOIN tbventas s ON s.id_venta=r.id_venta LEFT JOIN tbpedidos o ON o.id_pedido=s.id_pedido';
        $total=(int)$this->db->query('SELECT COUNT(*)'.$join)->fetchColumn();
        $search=$input['search']['value']??'';if(!is_string($search) || mb_strlen($search)>150)throw new InvalidArgumentException('Búsqueda inválida.');
        if($search!==''){$where[]="(s.codigo_venta LIKE ? OR o.codigo_pedido LIKE ?)";$pattern='%'.strtr($search,['='=>'==','%'=>'=%','_'=>'=_']).'%';$where[count($where)-1]="(s.codigo_venta LIKE ? ESCAPE '=' OR o.codigo_pedido LIKE ? ESCAPE '=')";$params[]=$pattern;$params[]=$pattern;}
        $filter=$where?' WHERE '.implode(' AND ',$where):'';
        $q=$this->db->prepare('SELECT COUNT(*)'.$join.$filter);$q->execute($params);$filtered=(int)$q->fetchColumn();
        $q=$this->db->prepare("SELECT COALESCE(SUM(CASE WHEN s.estado='pagada' THEN r.monto_venta ELSE 0 END),0) AS base,COALESCE(SUM(CASE WHEN s.estado='pagada' THEN r.monto_reposicion ELSE 0 END),0) AS reposicion,COALESCE(SUM(CASE WHEN s.estado='pagada' THEN r.monto_crecimiento ELSE 0 END),0) AS crecimiento,COALESCE(SUM(CASE WHEN s.estado='pagada' THEN r.total_reinversion ELSE 0 END),0) AS reinversion".$join.$filter);$q->execute($params);$summary=$q->fetch();
        $summary['missing']=(int)$this->db->query("SELECT COUNT(*) FROM tbventas s WHERE s.cobro_solicitud IS NOT NULL AND s.estado='pagada' AND NOT EXISTS(SELECT 1 FROM tbreinversiones r WHERE r.id_venta=s.id_venta)")->fetchColumn();
        $columns=['s.codigo_venta','o.codigo_pedido','r.created_at','r.monto_venta','r.monto_reposicion','r.monto_crecimiento','r.total_reinversion','s.estado'];$column=(int)($input['order'][0]['column']??2);$sort=$columns[$column]??'r.created_at';$direction=($input['order'][0]['dir']??'desc')==='asc'?'ASC':'DESC';$start=max(0,min(1000000,(int)($input['start']??0)));$length=max(1,min(100,(int)($input['length']??12)));
        $q=$this->db->prepare('SELECT r.*,s.codigo_venta,s.estado,s.cobro_solicitud,o.codigo_pedido'.$join.$filter.' ORDER BY '.$sort.' '.$direction.',r.id_reinversion DESC LIMIT '.$length.' OFFSET '.$start);$q->execute($params);
        return ['draw'=>max(0,(int)($input['draw']??0)),'recordsTotal'=>$total,'recordsFiltered'=>$filtered,'rows'=>$q->fetchAll(),'summary'=>$summary];
    }
}
