<?php
declare(strict_types=1);
// Usa las compras sintéticas del cierre; nunca escribe en la base del negocio.
$first=$sales->purchaseCards($cardOwner,[]);$second=$sales->purchaseCards($cardOwner,['page'=>'2']);
catalogCheck($first['total']===6 && count($first['items'])===4 && count($second['items'])===2 && $first['pages']===2,'compras propias: cuatro tarjetas por página y dos restantes');
catalogCheck(count(array_unique(array_column(array_merge($first['items'],$second['items']),'id_venta')))===6,'páginas de compras sin repetir ventas');
$last=$sales->purchaseCards($cardOwner,['page'=>'999']);catalogCheck($last['page']===2 && count($last['items'])===2,'página fuera de rango vuelve a la última página propia');
$code=$first['items'][0]['codigo_venta'];$filtered=$sales->purchaseCards($cardOwner,['q'=>$code]);
catalogCheck($filtered['total']===1 && $filtered['summary']['paid']===$first['items'][0]['total'],'búsqueda y resumen corresponden a la misma compra del propietario');
$strangerId=(int)$users->findActiveByEmail('other@orders.example.test')['id_usuario'];
catalogCheck($sales->purchaseCards($strangerId,['q'=>$code])['total']===0,'buscar un comprobante ajeno no revela su existencia');
[$status,$html]=catalogRequest('/purchases',$cardCookie);
catalogCheck($status===200 && substr_count($html,'class="orders-panel purchase-card"')===4 && !str_contains($html,'data-admin-datatable') && !str_contains($html,'admin-tables.js') && !str_contains($html,'/admin/cash'),'Mis compras muestra tarjetas sin DataTables ni herramientas de caja');
[$status,$html]=catalogRequest('/purchases?page=2',$cardCookie);catalogCheck($status===200 && substr_count($html,'class="orders-panel purchase-card"')===2 && str_contains($html,'Página 2 de 2'),'navegación HTTP a la segunda página de tarjetas');
[$status,$html]=catalogRequest('/purchases?status=pagada&page=2',$cardCookie);catalogCheck($status===200 && str_contains($html,'status=pagada&amp;page=1') && str_contains($html,'/sales/detail?id='),'paginación conserva filtros y acceso al comprobante');
[$status,$html]=catalogRequest('/purchases?q='.rawurlencode($code),$stranger);catalogCheck($status===200 && !str_contains($html,'class="orders-panel purchase-card"') && !str_contains($html,'/sales/detail?id=') && str_contains($html,'Todavía no tienes compras'),'cliente sin compras recibe estado vacío sin comprobantes ajenos');
[$status,$html]=catalogRequest('/purchases?status=anulada',$cardCookie);catalogCheck($status===200 && str_contains($html,'No encontramos compras con estos filtros'),'filtro sin resultados permite volver a todas las compras');
[$status,$html]=catalogRequest('/purchases?status=anulada',$client);catalogCheck($status===200 && str_contains($html,'purchase-state--refunded') && str_contains($html,'Reembolsada'),'compras anuladas se presentan como reembolsadas al cliente');
[$status,$html]=catalogRequest('/purchases?q='.rawurlencode('"><script>alert(1)</script>'),$cardCookie);catalogCheck($status===200 && !str_contains($html,'<script>alert(1)</script>') && str_contains($html,'&lt;script&gt;'),'texto de búsqueda escapado en el formulario');
foreach(['status[]=pagada','q[]=x','page[]=2','page=-1','from=2026-99-99','from=2026-10-20&to=2026-10-01'] as $query){[$status]=catalogRequest('/purchases?'.$query,$cardCookie);catalogCheck($status===422,'filtro de compras malformado rechazado: '.$query);}
echo "Mis compras: tarjetas, paginación, filtros, comprobantes y privacidad verificados.\n";
