let barcodeString = '';

function clearBarcodeString() {
    barcodeString = '';
}

function isNumeric(value) {
    // Use the isNaN function to check if the value is not a number
    return !isNaN(parseFloat(value)) && isFinite(value);
}

// Set an interval to clear the barcode string every 3 seconds
let clearingInterval = setInterval(clearBarcodeString, 3000);

// Listen for the "keydown" event on the document
document.addEventListener("keypress", function(event) {
    // Check if the pressed key is not "Enter" (key code 13)
    if (event.key !== "Enter") {
        // Append the pressed key to the barcode string
        barcodeString += event.key;
        clearInterval(clearingInterval);
        clearingInterval = setInterval(clearBarcodeString, 3000);
    } else {// If "Enter" key is pressed, log the complete barcode string
        const barcodeStringElements = barcodeString.split('*');
        const projectNo = barcodeStringElements[0];
        const panelNo = barcodeStringElements[1];

        if(!isNumeric(projectNo) || !isNumeric(panelNo)){
            clearBarcodeString();
            return;
        }

        window.location.href = './notes/detail.php?ProjectNo='+projectNo+'&PanelNo='+panelNo;
    }
});