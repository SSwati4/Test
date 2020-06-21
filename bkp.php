<?php
/*
Plugin Name: B2BJewellers
Plugin URI: https://demo.enkast.world/b2bjewellers/
Description: Custom plugin for B2B Jewellers
Version: 1.0.0
Author: enKast
Author URI: http://enKast.com/
License: GPL2
*/
register_activation_hook( __FILE__, 'eK_b2bjewellers');
function eK_b2bjewellers() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $table_name = $wpdb->prefix . 'b2bjewellers';
  $sql = "CREATE TABLE `$table_name` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(220) DEFAULT NULL,
  `price` varchar(220) DEFAULT NULL,
  `unit` varchar(220) DEFAULT NULL,
  PRIMARY KEY(id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}
add_action('admin_menu', 'addAdminPageContent');
function addAdminPageContent() {
  add_menu_page('B2B Jewellers', 'B2B Jewellers', 'manage_options' ,__FILE__, 'crudAdminPage', 'dashicons-wordpress');
}
function crudAdminPage() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'b2bjewellers';
  if (isset($_POST['newsubmit'])) {
    $name = $_POST['newname'];
    $price = $_POST['newprice'];
    $unit = $_POST['newunit'];
    $wpdb->query("INSERT INTO $table_name(name,price,unit) VALUES('$name','$price','$unit')");
    echo "<script>location.replace('admin.php?page=B2BJewellers/B2BJewellers.php');</script>";
  }
  if (isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $name = $_POST['uptname'];
    $price = $_POST['uptprice'];
    $unit = $_POST['uptunit'];
    $wpdb->query("UPDATE $table_name SET name='$name',price='$price',unit='$unit' WHERE id='$id'");
    echo "<script>location.replace('admin.php?page=B2BJewellers/B2BJewellers.php');</script>";
  }
  if (isset($_GET['del'])) {
    $del_id = $_GET['del'];
    $wpdb->query("DELETE FROM $table_name WHERE id='$del_id'");
    echo "<script>location.replace('admin.php?page=B2BJewellers/B2BJewellers.php');</script>";
  }
  ?>
  <div class="wrap">
    <h2>Operations</h2>
    <table class="wp-list-table widefat striped">
      <thead>
        <tr>
          <th width="25%">Item Type</th>
          <th width="25%">Unit Type</th>
          <th width="25%">Price Per Unit</th>
          <th width="25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post">
          <tr>
            <td><input type="text" id="newname" name="newname"></td>
            <td><input type="text" id="newunit" name="newunit"></td>
            <td><input type="text" id="newprice" name="newprice"></td>
            <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
          $result = $wpdb->get_results("SELECT * FROM $table_name");
          foreach ($result as $print) {
            echo "
              <tr>
                <td width='25%'>$print->name</td>
                <td width='25%'>$print->unit</td>
                <td width='25%'>$print->price</td>
                <td width='25%'><a href='admin.php?page=B2BJewellers/B2BJewellers.php&upt=$print->id'><button type='button'>UPDATE</button></a> <a href='admin.php?page=B2BJewellers/B2BJewellers.php&del=$print->id'><button type='button'>DELETE</button></a></td>
              </tr>
            ";
          }
        ?>
      </tbody>  
    </table>
    <br>
    <br>
    <?php
      if (isset($_GET['upt'])) {
        $upt_id = $_GET['upt'];
        $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$upt_id'");
        foreach($result as $print) {
          $name = $print->name;
          $price = $print->price;
        }
        echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
              <th width='25%'>Item Type</th>
              <th width='25%'>Unit Type</th>
              <th width='25%'>Price Per Unit</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td width='25%'><input type='text' id='uptname' name='uptname' value='$print->name'> <input type='hidden' id='uptid' name='uptid' value='$print->id'></td>
                <td width='25%'><input type='text' id='uptunit' name='uptunit' value='$print->unit'></td>
                <td width='25%'><input type='text' id='uptprice' name='uptprice' value='$print->price'></td>
                <td width='25%'><button id='uptsubmit' name='uptsubmit' type='submit'>UPDATE</button> <a href='admin.php?page=B2BJewellers/B2BJewellers.php'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
      }
    ?>
  </div>
  <?php
}

// fetch all of product

function return_custom_price($price, $product) {
    global $wpdb;
    $table_name1 = $wpdb->prefix . 'b2bjewellers';
    $query = "SELECT * FROM $table_name1";
    $results = $wpdb->get_results($query);
//    print_r($_REQUEST);
    $post = get_post();
    $post_id = $post->ID;
   
    $table_name = $wpdb->prefix . 'postmeta';
    $result = $wpdb->get_results("SELECT * FROM $table_name where post_id='$post_id'");
    $table_postmeta = $wpdb->prefix . 'postmeta';
    $table_post = $wpdb->prefix . 'posts';
    $table_b2bjewellers = $wpdb->prefix. 'b2bjewellers';

    $query1 = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON (p.id =pm.post_id AND p.id='$post_id') AND (pm.post_id='$post_id')  AND pm.meta_key='_item_type'");
    $price = $final_total = 0;
    if(!empty($query1)){
      $aa = (array)$query1[0];
      $decoded = json_decode($aa['meta_value']); 
      // var_dump($decoded);
      foreach ($decoded as  $value) {
         $final_total = $final_total + $value->total;
      }  
    // }
    // $myvals = get_post_meta($post_id);
    // $someJSON = $myvals['_product_attributes'][0];
    // $decoded = unserialize($someJSON);
    // $mul=0;
  //   if(!empty($decoded)){
  //     foreach ($decoded as $arr){
  //       print_r($arr);exit;
  //         foreach ($results as $res){
  //           echo "name". $user_val=$arr['name'];exit;
  //           $plug_val=$res->name;
  //           if($plug_val == $user_val){
  //             echo  $mul += ($arr['value'] * $res->price);
  //             }
  //         }
  //     }
  // }
    $price = $final_total;
     $table_update = $wpdb->prefix . 'postmeta';
     $check_price = $wpdb->get_results("SELECT * FROM $table_postmeta where post_id='$post_id' AND meta_key = '_price'");
     // var_dump($check_price);exit;
     if(empty($check_price)){
      
      $wpdb->query("INSERT into $table_postmeta  (post_id,meta_key,meta_value) values ($post_id,'_price',$price) ");
    
    }else
{
  
     $wpdb->query("UPDATE $table_postmeta SET meta_value='$price' WHERE post_id='$post_id' AND meta_key = '_price' ");
   }

    $check_regular_price = $wpdb->get_results("SELECT * FROM $table_postmeta where post_id='$post_id' AND meta_key = '_regular_price'");
    if(empty($check_regular_price)){
      $wpdb->query("INSERT into $table_postmeta  (post_id,meta_key,meta_value) values ($post_id,'_regular_price',$price) ");
    
    
    }
    else{
      $wpdb->query("UPDATE $table_postmeta SET meta_value='$price' WHERE post_id='$post_id' AND meta_key = '_regular_price' ");
      
    }
    }
    return $price;
}


function return_custom_price1($product) {
//     global $wpdb;
//     $table_name1 = $wpdb->prefix . 'b2bjewellers';
//     $query = "SELECT * FROM $table_name1";
//     $results = $wpdb->get_results($query);
//     $post_id = $product;
//     $table_postmeta = $wpdb->prefix . 'postmeta';
//     $table_post = $wpdb->prefix . 'posts';
//     $table_b2bjewellers = $wpdb->prefix. 'b2bjewellers';
    
//     $final_total = 0;
//     $query1 = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON (p.id =pm.post_id AND p.id='$post_id') AND (pm.post_id='$post_id')  AND pm.meta_key='_item_type'");
//     $price = $final_total = 0;
//     if(!empty($query1)){
//       $aa = (array)$query1[0];
//       $decoded = json_decode($aa['meta_value']); 
//       // var_dump($decoded);
//       if(!empty($decoded)){
//       foreach ($decoded as  $value) {
//     $result = $wpdb->get_results("SELECT * FROM $table_b2bjewellers where id='$value->id'"); 
      
//         // echo "<br>Price".$value->qty;
//         if(!empty($value->qty))
//           $final_total += (($result[0]->price)?$result[0]->price:0 * ($value->qty)?$value->qty:0);
//       }  
//     }
//     // }
//     // $myvals = get_post_meta($post_id);
//     // $someJSON = $myvals['_product_attributes'][0];
//     // $decoded = unserialize($someJSON);
//     // $mul=0;
//   //   if(!empty($decoded)){
//   //     foreach ($decoded as $arr){
//   //       print_r($arr);exit;
//   //         foreach ($results as $res){
//   //           echo "name". $user_val=$arr['name'];exit;
//   //           $plug_val=$res->name;
//   //           if($plug_val == $user_val){
//   //             echo  $mul += ($arr['value'] * $res->price);
//   //             }
//   //         }
//   //     }
//   // }
//     $price = $final_total;
//      $table_update = $wpdb->prefix . 'postmeta';
//      $check_price = $wpdb->get_results("SELECT * FROM $table_postmeta where post_id='$post_id' AND meta_key = '_price'");
//      // var_dump($check_price);exit;
//      if(empty($check_price)){
      
//       $wpdb->query("INSERT into $table_postmeta  (post_id,meta_key,meta_value) values ($post_id,'_price',$price) ");
    
//     }else
// {
  
//      $wpdb->query("UPDATE $table_postmeta SET meta_value='$price' WHERE post_id='$post_id' AND meta_key = '_price' ");
//    }

//     $check_regular_price = $wpdb->get_results("SELECT * FROM $table_postmeta where post_id='$post_id' AND meta_key = '_regular_price'");
//     if(empty($check_regular_price)){
//       $wpdb->query("INSERT into $table_postmeta  (post_id,meta_key,meta_value) values ($post_id,'_regular_price',$price) ");
    
    
//     }
//     else{
//       $wpdb->query("UPDATE $table_postmeta SET meta_value='$price' WHERE post_id='$post_id' AND meta_key = '_regular_price' ");
      
//     }
//     }
//     return $price;
}
// add_filter('woocommerce_get_price', 'return_custom_price', 10, 2);
add_action( 'woocommerce_update_product', 'return_custom_price', 10, 2 ); 
add_action( 'added_post_meta', 'return_custom_price', 10, 2 );

global $wpdb;
$table_posts = $wpdb->prefix . 'posts';
$result = $wpdb->get_results("SELECT id FROM $table_posts where post_type='product'");
if(!empty($result) && count($result) >0){
  foreach ($result as $res){
            return_custom_price1($res->id);
          }
}

// function product_price($price,$product){
//   if($product == 'product'){
//     $final = return_custom_price
//   }

// }


// add_filter('woocommerce_get_price', 'return_custom_price', 10, 2);
add_action( 'woocommerce_update_product', 'return_custom_price', 10, 2 ); 
add_action( 'added_post_meta', 'return_custom_price', 10, 2 );


// First Register the Tab by hooking into the 'woocommerce_product_data_tabs' filter
add_filter( 'woocommerce_product_data_tabs', 'add_my_custom_product_data_tab' );
function add_my_custom_product_data_tab( $product_data_tabs ) {
    $product_data_tabs['my-custom-tab'] = array(
        'label' => __( 'Item Type', 'woocommerce' ),
        'target' => 'my_custom_product_data',
        'class'     => array( 'show_if_simple' ),
    );
    return $product_data_tabs;
}

/** CSS To Add Custom tab Icon */
function wcpp_custom_style() {?>
<style>
#woocommerce-product-data ul.wc-tabs li.my-custom-tab_options a:before { font-family: WooCommerce; content: '\e006'; }
</style>
<?php 
}
add_action( 'admin_head', 'wcpp_custom_style' );



// functions you can call to output text boxes, select boxes, etc.
add_action('woocommerce_product_data_panels', 'woocom_custom_product_data_fields');

function woocom_custom_product_data_fields() {
    global $post,$wpdb;
    $table_name = $wpdb->prefix . 'b2bjewellers';
    $table_post = $wpdb->prefix . 'posts';
    $table_postmeta = $wpdb->prefix . 'postmeta';

    $id = get_the_ID();

    // print_r($b);exit;

?>
    <!-- // Note the 'id' attribute needs to match the 'target' parameter set above -->
     <div id = 'my_custom_product_data'
    class = 'panel woocommerce_options_panel' > 
       <div class = 'options_group' > 


        <table class="wp-list-table widefat striped" id="table_item_type">
      <thead>
        <tr>
          <th width="25%">Add Item Type</th>
          <th width="25%">Add Quantity</th>
          <th width="25%">Unit Type</th>
          <th width="25%">Total Amount</th>
        </tr>
      </thead>
      <tbody>
       
        <?php
            $query = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON (p.id =pm.post_id AND p.id='$id') AND (pm.post_id='$id')  AND pm.meta_key='_item_type'");
            if(!empty($query)){
    $a = (array)$query[0];
    $b = json_decode($a['meta_value']); 

            foreach ($b as $key => $value) {            
        $result2 = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$value->id'");
        // print_r($result);
          foreach ($result2 as $result) { 
           if($value->id == $result->id && !empty($value->qty) || $value->qty > 0 ){       
            echo "
              <tr>
                <td width='25%'><input type='hidden' id='itemtype_$result->id' class='itemtype' value='$result->id'>$result->name</td>
                <td width='25%'><input type='text' class='quantity' id='quantity_$result->id' name='qty+$result->id' value='$value->qty' onChange=calculateTotal(this.value,$result->id)></td>
                <td width='25%'><input type='hidden' id='unit_$result->id' class='unit' value='$result->price'>$result->unit</td>
                <td width='25%'><input type='hidden' id='total_input_$result->id' class='total' value='$value->total'><span id='total_$result->id'>$value->total</span></td>
              </tr>
            ";
          }else{
            echo "
              <tr>
                <td width='25%'><input type='hidden' id='itemtype_$value1->id' class='itemtype' value='$value1->id'>$value1->name</td>
                <td width='25%'><input type='text' class='quantity' id='quantity_$value1->id' name='qty+$value1->id' value='' onChange=calculateTotal(this.value,$value1->id)></td>
                <td width='25%'><input type='hidden' id='unit_$value1->id' class='unit' value=''>$value1->unit</td>
                <td width='25%'><input type='hidden' id='total_input_$value1->id' class='total' value=''><span id='total_$value1->id'></span></td>
              </tr>
            ";
          }
        }
          }
      }
       $result2 = $wpdb->get_results("SELECT * FROM $table_name");
           foreach ($result2 as  $value1) {
            if(!empty($b)){
              foreach ($b as $val) {
                if($val->id != $value1->id && empty($val->qty) || $val->qty<0)
                 echo "
              <tr>
                <td width='25%'><input type='hidden' id='itemtype_$value1->id' class='itemtype' value='$value1->id'>$value1->name</td>
                <td width='25%'><input type='text' class='quantity' id='quantity_$res->id' name='qty+$value1->id' value='' onChange=calculateTotal(this.value,$value1->id)></td>
                <td width='25%'><input type='hidden' id='unit_$value1->id' class='unit' value='$value1->price'>$value1->unit</td>
                <td width='25%'><input type='hidden' id='total_input_$value1->id' class='total' value=''><span id='total_$value1->id'></span></td>
              </tr>
            ";
              }
          
             }else{
               echo "
              <tr>
                <td width='25%'><input type='hidden' id='itemtype_$value1->id' class='itemtype' value='$value1->id'>$value1->name</td>
                <td width='25%'><input type='text' class='quantity' id='quantity_$res->id' name='qty+$value1->id' value='' onChange=calculateTotal(this.value,$value1->id)></td>
                <td width='25%'><input type='hidden' id='unit_$value1->id' class='unit' value='$value1->price'>$value1->unit</td>
                <td width='25%'><input type='hidden' id='total_input_$value1->id' class='total' value=''><span id='total_$value1->id'></span></td>
              </tr>
            ";
             }
          }
        // $res = $wpdb->get_row("SELECT * FROM $table_name WHERE id!='$value->id'");
        // print_r("SELECT * FROM $table_name WHERE id!='$value->id'");
        // // foreach ($result3 as $res) { 
        //    // if($value->id != $res->id ){       
        //     echo "
        //       <tr>
        //         <td width='25%'><input type='hidden' id='itemtype_$res->id' class='itemtype' value='$res->id'>$res->name</td>
        //         <td width='25%'><input type='text' class='quantity' id='quantity_$res->id' name='qty+$res->id' value='$value->qty' onChange=calculateTotal(this.value,$res->id)></td>
        //         <td width='25%'><input type='hidden' id='unit_$res->id' class='unit' value='$res->price'>$res->unit</td>
        //         <td width='25%'><input type='hidden' id='total_input_$res->id' class='total' value='$value->total'><span id='total_$res->id'>$value->total</span></td>
        //       </tr>
        //     ";
          // }
        // }
        ?>
      </tbody>  
    </table>
       </div>
        <input type="hidden" name="final_data" id="final_array" value="">
    </div><?php
}

function addfinaldata($post_id){
  global $wpdb;
  if(isset($_POST['final_data']) && !empty($post_id)){
    $finaldata = array();
    $finaldata = $_POST['final_data'];
    $table_name = $wpdb->prefix . 'postmeta';
    $result = $wpdb->get_results("SELECT * FROM $table_name where post_id='$post_id' AND meta_key='_item_type'");
    $a = str_replace('\\', '',$finaldata);
      if(count($result) > 0){ 
      $wpdb->query("UPDATE $table_name SET meta_value='$a' WHERE post_id='$post_id' AND meta_key = '_item_type' ");}
    else{
 
      $wpdb->query("INSERT into $table_name  (post_id,meta_key,meta_value) values ($post_id,'_item_type','$a') ");
    }
  }
}

add_action('woocommerce_process_product_meta','addfinaldata');


add_action( 'woocommerce_process_product_meta_simple', 'woocom_save_proddata_custom_fields'  );

function displayItemTypes(){
  global $wpdb;
  $table_postmeta = $wpdb->prefix . 'postmeta';
  $table_post = $wpdb->prefix . 'posts';
  $table_b2bjewellers = $wpdb->prefix. 'b2bjewellers';
   $id = get_the_ID();
    $query = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON (p.id =pm.post_id AND p.id='$id') AND (pm.post_id='$id')  AND pm.meta_key='_item_type'");
   //echo "SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON (p.id =pm.post_id AND p.id='$id') AND (pm.post_id='$id')  AND pm.meta_key='_item_type'";exit;
    $a = (array)$query[0];
    $b = json_decode($a['meta_value']); 
    ?>

        <table class="wp-list-table widefat striped" >
      <thead>
        <tr>
          <th width="25%"> Item Type</th>
          <th width="25%"> Quantity</th>
          <th width="25%"> Unit </th>
          <th width="25%"> Cost</th>
        </tr>
      </thead>
      <tbody>
       
        <?php
    foreach($b as $value) {
     // echo "SELECT * $table_b2bjewellers FROM  WHERE id='$value->id'";exit;
      $master = $wpdb->get_row("SELECT * FROM $table_b2bjewellers   WHERE id='$value->id'");
      if(!empty($value->qty) || $value->qty >0){
       echo "
              <tr>
                <td width='25%'>$master->name</td>
                <td width='25%'>$value->qty</td>
                <td width='25%'>$master->unit</td>
                <td width='25%'>$value->total</span></td>
              </tr>
            ";
      }
     }
     ?>
      </tbody>
        </table>
          <?php
}

add_shortcode('display_item_types', 'displayItemTypes');

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
  function calculateTotal(qty,id){
    var  value= document.getElementById("unit_"+id).value;
    var total = qty*value;
    // alert(total);
    document.getElementById("total_"+id).innerHTML =total;
     document.getElementById("total_input_"+id).value =total;
  createArray();
  }

function createArray()
{
    var TableData = new Array();

    $('#table_item_type tr').each(function(row, tr){
        TableData[row]={
            "id" : $(tr).find('td > input:eq(0)').val()
            , "qty" :$(tr).find('td > input:eq(1)').val()
            , "unit" : $(tr).find('td > input:eq(2)').val()
            , "total" : $(tr).find('td > input:eq(3)').val()
        }    
    }); 
    TableData.shift();  // first row will be empty - so remove
    var myJSON = JSON.stringify(TableData);
    document.getElementById("final_array").value =myJSON;
}
</script>


