
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
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY(id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
  if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}
function mysite_admin_menu(){
add_menu_page('B2B Jewellers', 'B2B Jewellers', 'manage_options', 'members-slug', 'crudAdminPage');
add_submenu_page( 'members-slug', 'Parent Item Type', 'Parent Item Type', 'manage_options', 'add-members-slug', 'masterItems');
add_submenu_page( 'members-slug', 'Import And Export', 'import And Export', 'manage_options', 'add-import-export-slug', 'importexport');
}
 
add_action('admin_menu', 'mysite_admin_menu');

function importexport(){
 ?>
 <form method="post">
   <input type="submit" name="exportdata" id="exportdata" value="Export">

 </form>
 <?php 
}
if(isset($_POST['exportdata'])){

  $filename = "toy_csv.csv";
  $fp = fopen('php://output', 'w');
  global $wpdb;
  $table_b2bjewellers = $wpdb->prefix . 'b2bjewellers';
  $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$table_b2bjewellers'";
$masterdata = $wpdb->get_results($query);
// print_r($masterdata);exit;
// while ($row = $masterdata) {
//   echo $header[] = $row[0];
// } 
// for ($i=0; $i <  ; $i++) { 
//   # code...
// }
// foreach ($masterdata as  (array)$value) {
//   // print_r($value);exit;
//   $header[] = $value;
//   fputcsv($fp, $value);
// }

header('Content-type: application/csv');
header('Content-Disposition: attachment; filename='.$filename);
fputcsv($fp, $header);

// $query = "SELECT * FROM $table_b2bjewellers";
// $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'");
// while($row = $result) {
//   fputcsv($fp, $row);
// }
}


function crudAdminPage() {
  session_start();

  global $wpdb;
  $table_name = $wpdb->prefix . 'b2bjewellers';
  if (isset($_POST['newsubmit'])) {
   $_SESSION["user_id_item"] = $newuserid = $_POST['newuserid'];
    $masterdata = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'");
    if(!empty($masterdata)){
     // var_dump($masterdata);exit;
     foreach ($masterdata as $value) {
         $result = $wpdb->get_row("SELECT * FROM $table_name WHERE name='$value->name' AND user_id = '$newuserid'");
    if(empty($result)){
        $wpdb->query("INSERT INTO $table_name(name,price,unit,user_id) VALUES('$value->name','$value->price','$value->unit','$newuserid')");
    
    }
    // else{
    //   echo "<script> alert('Item already exsits for this user'); </script>";
    // }
      } 
     // echo "<script>location.replace('admin.php?page=B2BJewellers/B2BJewellers.php');</script>";
    }
   
  }
  if (isset($_POST['uptsubmit'])) {
    $id = $_POST['uptid'];
    $price = $_POST['uptprice'];
    // $user_id = $_POST['uptuserid'];
    // $result = $wpdb->get_row("SELECT * FROM $table_name WHERE name='$name' AND user_id = '$newuserid'");
    // echo "SELECT * FROM $table_name WHERE name='$name' AND user_id = '$newuserid'";
    // if(empty($result)){
      $wpdb->query("UPDATE $table_name SET price='$price' WHERE id='$id'");
      echo "<script>location.replace('admin.php?page=B2BJewellers/B2BJewellers.php');</script>";
    // }else{
      // echo "<script> alert('Item already exsits for this user'); </script>";
    // }
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
          <th width="25">User</th>
          <th width="25%">Item Type</th>
          <th width="25%">Unit Type</th>
          <th width="25%">Price Per Unit</th>
          <th width="25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post">
          <tr>
             <td>
              <select id="newuserid" name="newuserid" required="">
                <option value="">Select User</option>
                <?php
                $table_user = $wpdb->prefix . 'users';
                $table_usermeta = $wpdb->prefix . 'usermeta';
                  $result = $wpdb->get_results("SELECT u.user_email,u.id,um.meta_value FROM $table_user as u JOIN $table_usermeta as um ON u.id =um.user_id  AND um.meta_key='first_name'");
                  
                  foreach ($result as $value) {
                     echo "<option value=$value->id ";
                    if ($_SESSION['user_id_item'] == $user_id) {
                      echo 'selected="selected"';
                    }
                    echo "<option value=$value->id>$value->meta_value($value->user_email)</option>";
                   }
                 ?>
              </select>  
            </td>

            <td></td>
            <td></td>
            <td></td>
            <td><button id="newsubmit" name="newsubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
        if(isset($_SESSION['user_id_item']) ){
            $user_id = $_SESSION['user_id_item'];
          $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='$user_id'");
          foreach ($result as $print) {
            $user = $wpdb->get_row("SELECT u.user_email,u.id,um.meta_value FROM $table_user as u JOIN $table_usermeta as um ON u.id =$print->user_id AND um.user_id=$print->user_id  AND um.meta_key='first_name'");
            echo "
              <tr>
              <td width='25%'>$user->meta_value ($user->user_email)</td>
                <td width='25%'>$print->name</td>
                <td width='25%'>$print->unit</td>
                <td width='10%'>$print->price</td>
                <td width='25%'><a href='admin.php?page=B2BJewellers/B2BJewellers.php&upt=$print->id&uptuserid=$user_id'><button type='button'>UPDATE</button></a> </td>
              </tr>
            ";
          }
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
          $user_id = $print->user_id;
        }
        echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
            <th width='25%'>User</th>
              <th width='25%'>Item Type</th>
              <th width='25%'>Unit Type</th>
              <th width='25%'>Price Per Unit</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td>
                <select id='uptuserid' name='uptuserid' disabled>
                ";
                  ?>
                  <?php
                    
                   $table_user = $wpdb->prefix . 'users';
                   $table_usermeta = $wpdb->prefix . 'usermeta';
                  $user = $wpdb->get_results("SELECT u.user_email,u.id,um.meta_value FROM $table_user as u JOIN $table_usermeta as um ON u.id =um.user_id  AND um.meta_key='first_name'");
                  
                  foreach ($user as $value) {
                    echo "<option value=$value->id ";
                    if ($value->id == $user_id) {
                      echo 'selected="selected"';
                    }
                    echo ">$value->meta_value ($value->user_email)</option>";
                   }
                    echo "
                </select>
                <td width='25%'><input type='text' id='uptname' disabled name='uptname' value='$print->name'> <input type='hidden' id='uptid' name='uptid' value='$print->id' ></td>
                <td width='25%'><input type='text' id='uptunit' name='uptunit' value='$print->unit' disabled></td>
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


function masterItems() {
  global $wpdb;
  $table_name = $wpdb->prefix . 'b2bjewellers';
  if (isset($_POST['mastersubmit'])) {
    $mastername = $_POST['mastername'];
    $masterunit = $_POST['masterunit'];
    $masterprice = $_POST['masterprice'];
    $masterdata = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0' AND name = '$mastername'");
    if(!empty($masterdata)){
      echo "<script> alert('Item already exsits for this user'); </script>"; 
     // echo "<script>location.replace('admin.php?page=B2BJewellers/B2BJewellers.php');</script>";
    }else{
      $wpdb->query("INSERT INTO $table_name(name,price,unit,user_id) VALUES('$mastername','$masterprice','$masterunit','0')");
    }
   
  }
  if (isset($_POST['uptmastersubmit'])) {
    $id = $_POST['uptmasterid'];
    $price = $_POST['uptmasterprice'];
    $name = $_POST['uptmastername'];
    $result = $wpdb->get_row("SELECT * FROM $table_name WHERE name='$name' AND user_id = '0'");
    // echo "SELECT * FROM $table_name WHERE name='$name' AND user_id = '$newuserid'";
    if(empty($result)){
      $wpdb->query("UPDATE $table_name SET price='$price' WHERE id='$id'");
      echo "<script>location.replace('admin.php?page=B2BJewellers/B2BJewellers.php');</script>";
    }else{
      echo "<script> alert('Item already exsits for this user'); </script>";
    }
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
          <th width="25">Sr No.</th>
          <th width="25%">Item Type</th>
          <th width="25%">Unit Type</th>
          <th width="25%">Price Per Unit</th>
          <th width="25%">Actions</th>
        </tr>
      </thead>
      <tbody>
        <form action="" method="post">
          <tr>
            <td></td>
            <td><input type="text" name="mastername"></td>
            <td><input type="text" name="masterunit"></td>
            <td><input type="number"min='0' name="masterprice"></td>
            
            <td><button id="mastersubmit" name="mastersubmit" type="submit">INSERT</button></td>
          </tr>
        </form>
        <?php
          $count = 1;
          $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id='0'");
          foreach ($result as $print) {
            $user = $wpdb->get_row("SELECT u.user_email,u.id,um.meta_value FROM $table_user as u JOIN $table_usermeta as um ON u.id =$print->user_id AND um.user_id=$print->user_id  AND um.meta_key='first_name'");
            echo "
              <tr>
              <td width='25%'>$count</td>
                <td width='25%'>$print->name</td>
                <td width='25%'>$print->unit</td>
                <td width='10%'>$print->price</td>
                <td width='25%'><a href='admin.php?page=add-members-slug&masterupt=$print->id'><button type='button'>UPDATE</button></a> </td>
              </tr>
            ";
            $count++;
          }
        
        ?>
      </tbody>  
    </table>
    <br>
    <br>
    <?php
      if (isset($_GET['masterupt'])) {
        $upt_id = $_GET['masterupt'];
        $result = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$upt_id'");
        foreach($result as $print) {
          $name = $print->name;
          $price = $print->price;
          $user_id = $print->user_id;
        }
        echo "
        <table class='wp-list-table widefat striped'>
          <thead>
            <tr>
            <th width='25%'>Sr No.</th>
              <th width='25%'>Item Type</th>
              <th width='25%'>Unit Type</th>
              <th width='25%'>Price Per Unit</th>
              <th width='25%'>Actions</th>
            </tr>
          </thead>
          <tbody>
            <form action='' method='post'>
              <tr>
                <td></td>
                
                <td width='25%'><input type='text' id='uptmastername' disabled name='uptname' value='$print->name'> <input type='hidden' id='uptmasterid' name='uptmasterid' value='$print->id' ></td>
                <td width='25%'><input type='text' id='uptmasterunit' name='uptmasterunit' value='$print->unit' disabled></td>
                <td width='25%'><input type='text' id='uptmasterprice' name='uptmasterprice' value='$print->price'></td>
                <td width='25%'><button id='uptmastersubmit' name='uptmastersubmit' type='submit'>UPDATE</button> <a href='admin.php?page=B2BJewellers/B2BJewellers.php'><button type='button'>CANCEL</button></a></td>
              </tr>
            </form>
          </tbody>
        </table>";
      }
    ?>
  </div>
  <?php
}



function return_custom_price($price, $product) {
    global $wpdb;
    $user_id = get_current_user_id();
    $post = get_post();
    $post_id = $product->get_id();    
    $table_b2bjewellers = $wpdb->prefix. 'b2bjewellers';
    $table_postmeta = $wpdb->prefix . 'postmeta';     
    $table_post = $wpdb->prefix . 'posts';
    $price = $final_total = 0;
    $query1 = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON (p.id =pm.post_id AND p.id='$post_id') AND (pm.post_id='$post_id')  AND pm.meta_key='_item_type'");
      if(!empty($query1)){
        $aa = (array)$query1[0];
        $decoded = json_decode($aa['meta_value']); 
        if(!empty($decoded)){
          foreach ($decoded as  $value) {
            $querya = $wpdb->get_row("SELECT * FROM $table_b2bjewellers WHERE  id = '$value->id'");
            $query = $wpdb->get_row( "SELECT * FROM $table_b2bjewellers WHERE user_id = '$user_id' AND name = '$querya->name'");
             // echo $querya->name.''.$value->qty . '' . $query->price .'<br>';
            if(!empty($query)){
             $final_total = $final_total + ($value->qty * $query->price);
            }
           else{
            $querya = $wpdb->get_row("SELECT * FROM $table_b2bjewellers WHERE  id = '$value->id'");
            $masterquery = $wpdb->get_row("SELECT * FROM $table_b2bjewellers WHERE user_id = '0' AND name = '$querya->name'");
              // echo $querya->name.''.$value->qty . '' . $masterquery->price .'<br>';
            // print_r($value->qty);
            if(!empty($masterquery)){
              $final_total = $final_total + ($value->qty * $masterquery->price);
            }
           }

          }  
          $price = $final_total;
        }
      
    }  
    // echo $price;
    return $price; 
}  

add_filter('woocommerce_product_get_price', 'return_custom_price', 10, 2);

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
    $user_id = get_current_user_id();
    $query = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON p.id =pm.post_id AND pm.meta_key='_item_type'");
    $a = (array)$query[0];
    $b = json_decode($a['meta_value']); 

?>
    <!-- // Note the 'id' attribute needs to match the 'target' parameter set above -->
     <div id = 'my_custom_product_data'
    class = 'panel woocommerce_options_panel' > 
       <div class = 'options_group' > 
        <?php
          $dbData = array();
          $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = '$user_id'");
          $query = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON (p.id =pm.post_id AND p.id='$id') AND (pm.post_id='$id')  AND pm.meta_key='_item_type'");
          if(!empty($query)){
            $a = (array)$query[0];
            $b = json_decode($a['meta_value']); 
            if(!empty($b)){
              foreach ($b as $key => $value) {   
                array_push($dbData,$value->id);
              }
            }
          }

         ?>
        <select id="masteritem" class="select">
          <option value="0">Select Item Type</option>
          <?php 

          if(empty($result))
            $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = '0'");
          foreach ($result as $print) {
            // foreach ($b as  $value) {
            //   var_dump($b);exit;
            //   if($value->id != $print->id){
            
            
            
          if(!in_array($print->id,$dbData))
          echo "
          <option id='$print->id' value='$print->id".'_'."$print->price".'_'."$print->name".'_'."$print->unit'>$print->name</option>";
        // }
        // } 
}
        ?>
        </select>
        <a id="appendrow" class="button tagadd" onclick="appendRow()">Add Row</a>
        <div id="empty_select"></div>
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
          // var_dump($b);exit;
            if(!empty($b)){
              foreach ($b as $key => $value) {            
                // var_dump($value->id);
              $result2 = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$value->id'  ");
               //echo "SELECT * FROM $table_name WHERE id='$value->id' AND user_id = '$user_id' ";exit;
          foreach ($result2 as $result) {      
            if($value->id == $result->id && !empty($value->qty) || $value->qty > 0 ){      
            echo "
            <tr>
            <td width='25%'><input type='hidden' id='itemtype_$result->id' class='itemtype' value='$result->id'>$result->name</td>
            <td width='25%'><input type='number' class='quantity' id='quantity_$result->id' name='qty+$result->id' value='$value->qty' onChange=calculateTotal(this.value,$result->id)></td>
            <td width='25%'><input type='hidden' id='unit_$result->id' class='unit' value='$result->price'>$result->unit</td>
            <td width='25%'><input type='hidden' id='total_input_$result->id' class='total' value='$value->total'><span id='total_$result->id'>$value->total</span></td>
          </tr>
            ";
            }
          }
        }
      }
      }
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
    
    $a = (array)$query[0];
    $b = json_decode($a['meta_value']); 
    if(!empty($b)){
    ?>
    <div id="radio"></div>
    <div id="contents"></div>
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
   }
     ?>
      </tbody>
        </table>
          <?php
}

add_shortcode('display_item_types', 'displayItemTypes');

 function b2b_deactivate(){
   global $wpdb;
  $table_postmeta = $wpdb->prefix . 'postmeta';
  $table_b2bjewellers = $wpdb->prefix. 'b2bjewellers';
  $sql = "DROP TABLE IF EXISTS $table_b2bjewellers;";
  $wpdb->query($sql);
  $delete_data = "DELETE from $table_postmeta where meta_key = '_item_type'";
  $wpdb->query($delete_data);
     delete_option("my_plugin_db_version");
 }

add_action('woocommerce_before_add_to_cart_button','wdm_add_custom_fields');

function wdm_add_custom_fields()
{

    global $product;

    ob_start();

    
        global $post,$wpdb;
    $table_name = $wpdb->prefix . 'b2bjewellers';
    $table_post = $wpdb->prefix . 'posts';
    $table_postmeta = $wpdb->prefix . 'postmeta';
    $id = get_the_ID();
    $user_id = get_current_user_id();
    $query = $wpdb->get_results("SELECT pm.meta_value FROM $table_post as p JOIN $table_postmeta as pm ON p.id =pm.post_id AND pm.meta_key='_item_type'");
    $a = (array)$query[0];
    $b = json_decode($a['meta_value']); 

?>
    <!-- // Note the 'id' attribute needs to match the 'target' parameter set above -->
     <div id = 'my_custom_product_data'
    class = 'panel woocommerce_options_panel' > 
       <div class = 'options_group' > 
        <?php
          $dbData = array();
          $result = $wpdb->get_results("SELECT * FROM $table_name ");
          

         ?>
       
        <!-- <a id="appendrow" class="button tagadd" onclick="appendRow()">Add Row</a> -->
        <div id="empty_select"></div>
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
         
          foreach ($result as $result) {      
            // if($value->id == $result->id && !empty($value->qty) || $value->qty > 0 ){      
            echo "
            <tr>
            <td width='25%'><input type='hidden' id='itemtype_$result->id' class='itemtype' value='$result->id'>$result->name</td>
            <td width='25%'><input type='number' class='quantity' id='quantity_$result->id' name='qty+$result->id' value='$value->qty' onChange=calculatePrice(this.value,$result->id)></td>
            <td width='25%'><input type='hidden' id='unit_$result->id' class='unit' value='$result->price'>$result->unit</td>
            <td width='25%'><input type='hidden' id='total_input_$result->id' class='total' value='$value->total'><span id='total_$result->id'>$value->total</span></td>
          </tr>
            ";
            }
          // }
        
        ?>
      </tbody>  
    </table>
    <p id="estimated_price"></p>
    <p class="form-row validate-required" id="image" >
        <label for="file_field"><?php echo __("Upload Image") . ': '; ?>
            <input type='file' name='image[]' accept='image/*' multiple>
        </label>
    </p>
       </div>
        <input type="hidden" name="wdm_name" id="final_array" value="">
        <input type="hidden" name="estimated_price" id="estimated_price_id" value="">
    </div>
    <?php

    $content = ob_get_contents();
    ob_end_flush();

    return $content;
}

add_filter('woocommerce_add_cart_item_data','wdm_add_item_data',10,3);

/**
 * Add custom data to Cart
 * @param  [type] $cart_item_data [description]
 * @param  [type] $product_id     [description]
 * @param  [type] $variation_id   [description]
 * @return [type]                 [description]
 */
function wdm_add_item_data($cart_item_data, $product_id, $variation_id)
{
     if( isset($_FILES['image']) && ! empty($_FILES['image']) ) {
       // foreach ($_FILES['image'] as $key => $value) {
       //   if ($_FILES['name'][$key]) {
      // var_dump($_FILES);exit;
      foreach($_FILES['image']['name'] as $key=>$value){
        // echo $key;
        // var_dump($_FILES['image']['name']);
    if ($_FILES['image']['name'][$key]) {
        $upload       = wp_upload_bits( $_FILES['image']['name'][$key], null, file_get_contents( $_FILES['image']['tmp_name'][$key] ) );
        $filetype     = wp_check_filetype( basename( $upload['file'][$key] ), null );
        $upload_dir   = wp_upload_dir();
        $upl_base_url = is_ssl() ? str_replace('http://', 'https://', $upload_dir['baseurl']) : $upload_dir['baseurl'];
        $base_name    = basename( $upload['file'][$key]);
        // print_r($upload);
        $cart_item_data['file_upload'][] = array(
            'guid'      => $upl_base_url .'/'. _wp_relative_upload_path( $upload['file'] ), // Url
            'url' => $upload['url'],
            'file_type' => $filetype['type'][$key], // File type
            'file_name' => $base_name, // File name
            'title'     => ucfirst( preg_replace('/\.[^.]+$/', '', $base_name ) ), // Title
        );
      }
    }
    // var_dump($cart_item_data);
        $cart_item_data['unique_key'] = md5( microtime().rand() ); // Avoid merging items
      //   } 
      // }
    }
    if(isset($_REQUEST['wdm_name']))
    {
        $cart_item_data['wdm_name'] = sanitize_text_field($_REQUEST['wdm_name']);
    }
     if(isset($_REQUEST['estimated_price']))
    {
        $cart_item_data['estimated_price'] = sanitize_text_field($_REQUEST['estimated_price']);
    }

    return $cart_item_data;
}


add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );

function add_custom_price( $cart_object ) {
    $custom_price = 10; // This will be your custome price  
    // var_dump($cart_object);
    foreach ( $cart_object->cart_contents as $key => $value ) {
        // $value['data']->price = $custom_price;
        // for WooCommerce version 3+ use: 
        $value['data']->set_price($custom_price);
    }
}

add_filter('woocommerce_get_item_data','wdm_add_item_meta',10,2);

/**
 * Display information as Meta on Cart page
 * @param  [type] $item_data [description]
 * @param  [type] $cart_item [description]
 * @return [type]            [description]
 */
function wdm_add_item_meta($item_data, $cart_item)
{
// print_r($cart_item['file_upload']);
  if(!empty($cart_item['file_upload'])){
foreach($cart_item['file_upload'] as $key=>$value){
if ( isset( $value['title'] ) ){
        // $item_data[] = array(
        //     'name' => __( 'Image uploaded', 'woocommerce' ),
        //     'value' =>  str_pad($value['title'], 16, 'X', STR_PAD_LEFT) . '…',
        // );
  $item_data[] = array(
            'name' => __( 'Image uploaded', 'woocommerce' ),
            'value' =>  '<img src="'. $value["guid"].'">',
        );
    }
//     $item_data[] = '<img src="'. $value["guid"].'">';
  }
}
global $wpdb;
$table_b2bjewellers = $wpdb->prefix. 'b2bjewellers';
    if(array_key_exists('wdm_name', $cart_item))
    {
        $custom_details = $cart_item['wdm_name'];
        $b = $custom_details;
            $b = json_decode(stripslashes($b)); 
          // var_dump($b);exit;
            // if(!empty($b)){
            //   foreach ($b as $key => $value) {  
            //    // echo "sdas";
            //    echo $value->total;
            //   }}

    ?>
<table>
  <thead>
    <th>Metal</th>
    <th>Quantity</th>
    <th>Unit</th>
    <th>Price</th>
  </thead>
  <tbody>
    
  
    <?php 
    
     if(!empty($b)){
        foreach ($b as $key => $value) {
          // echo $value;
          $master = $wpdb->get_row("SELECT * FROM $table_b2bjewellers   WHERE id='$value->id'");
          echo "<tr><td>".$master->name."</td>";
          echo "<td>".$value->qty."</td>";
          echo "<td>".$master->unit."</td>";
          echo "<td>".$value->total."</td><tr>";
        }
      }
        ?>
        </tbody>
  </table>
        <?php
        $item_data[] = array(
            'key'   => 'Name',
            'value' => $custom_details
        );
        $item_data[] = array(
            'key'   => 'Estimation',
            'value' => $cart_item['estimated_price']
        );
    }

    return $item_data;
}


// Admin new order email: Display a linked button + the link of the image file
add_action( 'woocommerce_email_after_order_table', 'wc_email_new_order_custom_meta_data', 10, 4);
function wc_email_new_order_custom_meta_data( $order, $sent_to_admin, $plain_text, $email ){
    // On "new order" email notifications
    if ( 'new_order' === $email->id ) {
        foreach ($order->get_items() as $item ) {
            if ( $file_data = $item->get_meta( '_img_file' ) ) {
                echo '<p>
                    <a href="'.$file_data['guid'].'" target="_blank" class="button">'.__("Download Image") . '</a><br>
                    <pre><code style="font-size:12px; background-color:#eee; padding:5px;">'.$file_data['guid'].'</code></pre>
                </p><br>';
            }
        }
    }
}

add_action( 'woocommerce_checkout_create_order_line_item', 'wdm_add_custom_order_line_item_meta',10,4 );

function wdm_add_custom_order_line_item_meta($item, $cart_item_key, $values, $order)
{
    if ( isset( $values['file_upload'] ) ){
      // print_r($values['file_upload']);exit;
        $item->update_meta_data( '_img_file',  $values['file_upload'] );
    }
    if(array_key_exists('wdm_name', $values))
    {
      $a = str_replace('\\', '',$values['wdm_name']);
        $item->add_meta_data('_wdm_name',$a,true);
    }
    if(array_key_exists('estimated_price', $values))
    {
     
        $item->add_meta_data('_estimated_price',$values['estimated_price'],true);
    }
}


// Admin orders: Display a linked button + the link of the image file
add_action( 'woocommerce_after_order_itemmeta', 'backend_image_link_after_order_itemmeta', 10, 3 );
function backend_image_link_after_order_itemmeta( $item_id, $item, $product ) {
    // Only in backend for order line items (avoiding errors)
    if( is_admin() && $item->is_type('line_item') && $file_data = $item->get_meta( '_img_file' )){
      foreach ($item->get_meta( '_img_file' ) as $key => $value) {
        echo '<p><a href="'.$value['guid'].'" target="_blank" class="button">'.__("Open Image") . '</a></p>'; // Optional
        // echo '<p><code>'.$value['guid'].'</code></p>'; // Optional
        echo '<img src="'.$value['guid'].'" style="height:10%; width:30%">';
      }
    }
    if( is_admin() && $item->is_type('line_item') ){
      global $wpdb;
$table_b2bjewellers = $wpdb->prefix. 'b2bjewellers';
      ?>
      <table>
  <thead>
    <th>Metal</th>
    <th>Quantity</th>
    <th>Unit</th>
    <th>Price</th>
  </thead>
  <tbody>
    <?php
      $b = json_decode(stripslashes($item->get_meta( '_wdm_name' )));
      if(!empty($b)){
        foreach ($b as $key => $value) {
          // echo $value;
          $master = $wpdb->get_row("SELECT * FROM $table_b2bjewellers   WHERE id='$value->id'");
          echo "<tr><td>".$master->name."</td>";
          echo "<td>".$value->qty."</td>";
          echo "<td>".$master->unit."</td>";
          echo "<td>".$value->total."</td><tr>";
        }
      }  
      ?>
    </tbody>
  </table>
  <h3>Estimated Price: <?php echo $item->get_meta( '_estimated_price' )?></h3>
      <?php  
      }
}



register_deactivation_hook( __FILE__, 'b2b_deactivate' );


wp_enqueue_script ( 'custom-script', plugin_dir_url( __FILE__ ) . 'b2b.js' );