var printCodeJson; // Declare a global variable to hold the JSON data

// Fetch the JSON data on page load
// Fetch the JSON data on page load
window.addEventListener('load', (event) => {
  fetch("catalog/view/javascript/print_codes.json")
    .then(response => response.json())
    .then(data => {
      printCodeJson = data; // Store the JSON data in the global variable

      // Iterate over the printCodeJson to fetch the updated prices
      printCodeJson.forEach((printCode, index) => {
        $.ajax({
          url: 'index.php?route=product/product.getprice',
          type: 'post',
          data: "product_model=" + encodeURIComponent(printCode.productModel),
          dataType: 'json',
          contentType: 'application/x-www-form-urlencoded',
          cache: false,
          processData: false,
          success: function (json) {
            if (json['price']) {
              // Update the price in the printCodeJson
              printCode.setupCharge = json['price'];
            } 

            if (json['product_id']) {
              printCode.productId = json['product_id'];
            } else {
              printCode.productId = 0;
            }
          }
        });

        printCode.amountColor.forEach((amountColor) => {
          amountColor.amountSetupCharges.forEach((amountSetupCharge) => {
            amountSetupCharge.decoPrices.forEach((decoPriceObj) => {
              decoPriceObj.decoPrice.forEach((decoPrice) => {
                // Replace this URL with the endpoint that provides the updated price
                var updatePriceUrl = "index.php?route=product/product.getprice";
                
                // Send an AJAX request to fetch the updated price for the given decoPrice

                $.ajax({
                  url: 'index.php?route=product/product.getprice',
                  type: 'post',
                  data: "product_model=" + encodeURIComponent(amountColor.productModel),
                  dataType: 'json',
                  contentType: 'application/x-www-form-urlencoded',
                  cache: false,
                  processData: false,
                  success: function (json) {
                    if (json['price']) {
                      // Update the price in the printCodeJson
                      decoPrice.price = json['price'];
                    } 
                    if (json['discount'] && json['discount'][decoPrice.priceFromQty] && (json['discount'][decoPrice.priceFromQty] >= decoPrice.priceFromQty)) {
                      decoPrice.price = json['discount'][decoPrice.priceFromQty];
                    }

                    if (json['product_id']) {
                      amountColor.productId = json['product_id'];
                    } else {
                      amountColor.productId = 0;
                    }
                  }
                });
              });
            });
          });
        });
      });
    });
});


function calculateTotalProductPrice(quantity, baseprice) {
  let baseTotal = parseFloat(baseprice) * Math.floor(quantity);
  let totalPrice = 0.0;
  let htmlToAppend = `<br>Startavgift: ${baseTotal.toFixed(2)}`;
  
  let selectElements = document.querySelectorAll('.print_calculations');
  
  selectElements.forEach((selectElement) => {
    let selectedId = selectElement.value;
    if (selectedId === 'Please select Print') return;
  
    let label = document.querySelector(`label[for="${selectElement.id}"]`).textContent;
    let matchingAmount = printCodeJson.find(printCodeObj => 
      printCodeObj.amountColor.some(amountColor => 
        `${printCodeObj.printCode}_${amountColor.amountColorsId}` === selectedId
      )
    );
  
    if (matchingAmount) {
      let setupCharge = parseFloat(matchingAmount.setupCharge);
      let amountColor = matchingAmount.amountColor.find(amountColor => 
        `${matchingAmount.printCode}_${amountColor.amountColorsId}` === selectedId
      );
      
      let decoPrice = amountColor.amountSetupCharges[0].decoPrices[0].decoPrice.find(priceObj => 
        quantity >= priceObj.priceFromQty
      );
      decoPrice = parseFloat(decoPrice?.price || 0);
  
      let decoPriceQuantity = decoPrice * Math.floor(quantity);
      totalPrice += setupCharge + decoPriceQuantity;
  
      htmlToAppend += `
        <br><b>${label}: ${matchingAmount.method} ${amountColor.amountColorsName}</b>
        <br>Setup Charge: ${setupCharge.toFixed(2)}
        <br>Deco Price: ${decoPrice.toFixed(2)}
        <br>Deco Price * quantity: ${decoPriceQuantity.toFixed(2)}
        <br>Total Price for ${quantity} quantity: ${totalPrice.toFixed(2)}`;
    }
  });

  totalPrice += baseTotal;
  
  if(isNaN(totalPrice)){
    $("#price_breakup").html(`<br>Startavgift: 0`);
    $("#total_price_breakup").html('0');
  }else{
    $("#price_breakup").html(htmlToAppend);
    $("#total_price_breakup").html(totalPrice.toFixed(2));
  }
  
  return totalPrice.toFixed(2);
}

  

