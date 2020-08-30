
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

    jQuery('#table_item_type tr').each(function(row, tr){
        TableData[row]={
            "id" : jQuery(tr).find('td > input:eq(0)').val()
            , "qty" :jQuery(tr).find('td > input:eq(1)').val()
            , "unit" : jQuery(tr).find('td > input:eq(2)').val()
            , "total" : jQuery(tr).find('td > input:eq(3)').val()
        }    
    }); 
    TableData.shift();  // first row will be empty - so remove
    var myJSON = JSON.stringify(TableData);
    document.getElementById("final_array").value =myJSON;
}

function appendRow() {

  var id = document.getElementById('masteritem').value;
  if(id != 0){
    var div = document.getElementById('empty_select').innerHTML = "";
    var res = id.split("_");
    var unique = res[0];
    var price = res[1];
    var item = res[2];
    var unit = res[3];
     jQuery('#table_item_type tbody').append(
      '<tr class=""><td><input type="hidden" value='+unique+' id=itemtype_'+ unique+'>'+ item +'</td> <td><input type="number" id=quantity_'+ unique+' onChange=calculateTotal(this.value,'+ unique +')></td> <td><input type="hidden"  id=unit_'+ unique+' value="'+ price +'">'+ unit +'</td> <td><input type="hidden"  id=total_input_'+ unique+' value=""> <span id="total_' + unique + '"></span></td></tr>'
      );
      jQuery("#masteritem option[id='" + unique + "']").remove();
    }
    else{
      var div = document.getElementById('empty_select').innerHTML = "<span> Please select item type</span>";
    }
}
function calculatePrice(qty,id){
  calculateTotal(qty,id);
  var array = document.getElementById("final_array").value;
  var sum = 0;
   var parsedJSON = JSON.parse(array);
       for (var i=0;i<parsedJSON.length;i++) {
            sum = Number(sum) + Number(parsedJSON[i].total);
         }
         var div = document.getElementById('estimated_price').innerHTML = "<span> Estimated Price: " + sum + "</span>";
         document.getElementById("estimated_price_id").value = sum;
}
productDesign();
function productDesign(){
let data = [{"id":"1","qty":"12","unit":"99","total":"1188"}];
let html = '';
let htmlRadio = '';
data.forEach(function(item){
  htmlRadio += '<input type="radio" onClick="productItem('+item.id+')" name="radio" class="radioCheck" value="'+item.id+'"/>'+item.name;
  html += '<div id="'+item.id+'"class="contents" style="display:none"><p>Quantity: '+item.qty+'</p></div> '
})
document.getElementById('radio').innerHTML += htmlRadio;
document.getElementById('contents').innerHTML += html;
}

function productItem(id){
  jQuery('.contents').css({display:none});
  jQuery('#'+id).css({display:block});
}