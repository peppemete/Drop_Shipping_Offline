<?php
/*
Plugin Name: Drop Shipping offline
Version: 1.0
Description: Genera un report giornaliero dei prodotti venduti, e li divide tra prodotti presenti in stock, e prodotti venduti in dropshipping, e lo invia tramite mail.
Author: Giuseppe Mete
Author URI: http://giemme.netlify.app
*/

include '../../../wp-config.php';
$mail = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name ='dso_mail'");
$abilita = $wpdb->get_var("SELECT option_value FROM $wpdb->options WHERE option_name ='dso_abilita'");;
if ($mail==null || $abilita =='null'){
    $wpdb->insert($wpdb->options,['option_name'=>'dso_mail']);
    $wpdb->insert($wpdb->options,['option_name'=>'dso_abilita']);
}

/**Crea Backend */
function DropMenu(){
    add_menu_page('Drop Shipping Offline', 'Drop Shipping Offline', 'administrator', 'DS Offline Dashboard', 'Drop_Shipping');
    
}
add_action('admin_menu', 'DropMenu');

function Drop_Shipping(){

    global $mail;
    global $abilita;
    $oggi = Date('Y/m/d');
    

    
    
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="../wp-content/plugins/Drop_Shipping_Offline/CSS/style_backend.css">
    </head>
    <body>
    <div class="body">
    <h1>Drop Shipping Offline - Dashboard</h1>
    <form action="../wp-content/plugins/Drop_Shipping_Offline/modifica_proprieta.php" method="post">
    <h2>Impostazioni servizio e-mail</h2>
    <div>
    <p>Insersci Mail: </p>
    <input type="text" name="mail" value=" ' . $mail . ' " />
    </div>
    <div>
    <p>Attiva Servizio: </p>
    <div class="radio">
    <input type="radio" id="check_si" name="check" value="si"';
    if($abilita=='si') echo ' checked ';
    echo '>
    Si
    </div>
    
    <div class="radio">
    <input type="radio" id="check_no" name="check" value="no"';
    if($abilita!='si')  echo ' checked ';
    echo '>
    No
    </div>
    
    </div>
    
    <input type="submit" value="Aggiorna Dati">
    </form>
    
    <form action="../wp-content/plugins/Drop_Shipping_Offline/Genera_Report.php" method="post" target="_blank">
    <h2>Genera report del giorno</h2>
    <div>
    <p>Insersci la data: </p>
    <input type="date" name="data" value=" ' . $oggi . ' " />
    </div>
    
    <input type="submit" value="Genera">
    </form>
    <p class="copyright">©Giuseppe Mete</p>
    </div>
    
    
    </body>' ;
}

function ciao(){
    echo "ciao";
}


/**Crea Report */
function creaReport($d){
    global $wpdb;
    $dropShipping;
    $inStock;
    if($d==0){
        $d=Date('Y/m/d') ;
    }

    $rows = $wpdb->get_results(
        
        "SELECT ID id, order_item_id order_id, order_item_type, order_item_name
        FROM $wpdb->posts , {$wpdb->prefix}woocommerce_order_items
        WHERE post_type='shop_order' AND order_id LIKE ID AND order_item_type <> 'shipping' AND Date(post_date) = '$d'
        "
    
    );
    foreach($rows as $row){
        $ordine=[];
        
        $id_order=$row->id;
        $id_single_item_ordered=$row->order_id;
        $order_item_type=$row->order_item_type;
        $product_name=$row->order_item_name;  
        $itemselled = $wpdb->get_results(
            
            "SELECT A.meta_value product_id, B.meta_value qty
            FROM {$wpdb->prefix}woocommerce_order_itemmeta A, {$wpdb->prefix}woocommerce_order_itemmeta B
            WHERE A.meta_key='_product_id' AND B.meta_key='_qty' AND A.order_item_id = {$id_single_item_ordered} AND B.order_item_id = {$id_single_item_ordered}"
        );
        $id_product = $itemselled[0]->product_id;
        $qty =$itemselled[0]->qty;
        $productinfo = $wpdb->get_results(
            "SELECT  A.meta_value stock
            FROM $wpdb->postmeta A, $wpdb->postmeta B
            WHERE A.meta_key='_stock_status' AND A.post_id= {$id_product} "
        );
    
        $stock = $productinfo[0]->stock;
    
        $shippinginfo = $wpdb->get_results(
            "SELECT A.meta_value addr
            FROM $wpdb->postmeta A
            WHERE A.meta_key='_shipping_address_index' AND A.post_id = {$id_order}"
        );
    
        $address = $shippinginfo[0]->addr;

        $ordine=[
            'numeroOrdine' => $id_order,
            'numeroProdottoOrdine' => $id_single_item_ordered,
            'numeroProdotto' => $id_product,
            'nomeProdotto' => $product_name,
            'qta' => $qty,
            'stock' => $stock ,
            'indirizzo' => $address 
        ];

        
        if($stock=='instock'){
            $inStock[]=$ordine;
        }
        else{
            $dropShipping[]=$ordine;
            
        }

        
        
        
        
        
        
    }
    $return= '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Report - data ' . $d . '</title><link rel="stylesheet" href="CSS/style_tab.css">
    </head>' ;
    $return.= '<body>
    <h1>Report giornaliero dei prodotti venduti</h1>
    <h2>Data: ' . $d . '</h2> 
    <div>
    <h3>Prodotti venduti in DropShipping</h3>
    <div class="tab">
    <div class="row">
    <div class="title">ID ordine</div>
    <div class="title">ID Prodotto</div>
    <div class="title">Nome Prodotto</div>
    <div class="title">Quantità</div>
    <div class="title">Indirizzo</div>
    </div>'
    ;
    
    foreach($dropShipping as $x){
        $return.= '<div class="row"> <div>' . $x['numeroOrdine'] . "</div><div>" . $x['numeroProdotto'] . '</div><div>' . $x['nomeProdotto'] . "</div><div>" . $x['qta'] . '</div><div>' .$x['indirizzo'] . "</div></div>";
    }
    $return.= '</div> </div>';

    $return.= ' 
    <div>
    <h3>Prodotti venduti in Stock</h3>
    <div class="tab">
    <div class="row">
    <div class="title">ID ordine</div>
    <div class="title">ID Prodotto</div>
    <div class="title">Nome Prodotto</div>
    <div class="title">Quantità</div>
    <div class="title">Indirizzo</div>
    </div>'
    ;
    foreach($inStock as $x){
        $return.= '<div class="row"> <div>' . $x['numeroOrdine'] . "</div><div>" . $x['numeroProdotto'] . '</div><div>' . $x['nomeProdotto'] . "</div><div>" . $x['qta'] . '</div><div>' .$x['indirizzo'] . "</div></div>";
    }
    $return.= '</div> </div> </body> </html>';

    return $return;
    
}

?>
