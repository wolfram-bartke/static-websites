<?php
$sOhneCache = ' style="display:none;"';
$sMitCache = '';
$sCacheHinweis = 'Default Settings';
if(!empty($_GET['cache'])){
    $sOhneCache = '';
    $sMitCache = ' style="display:none;"';
    $sCacheHinweis = 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0';
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
}
#header("Cache-Control: post-check=0, pre-check=0", false);
#header("Pragma: no-cache");

$iTime = time();
?>

<script>
// Send transaction data with a pageview if available
// when the page loads. Otherwise, use an event when the transaction
// data becomes available.
var dataLayer = [];
//dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
dataLayer.push({
  'ecommerce': {
    'purchase': {
      'actionField': {
        'id': '<?php print $iTime ?>',                // Transaction ID. Required for purchases and refunds.
        'affiliation': 'Online Store',
        'revenue': '35.43',                     // Total transaction value (incl. tax and shipping)
        'tax':'4.90',
        'shipping': '5.99',
        'coupon': 'SUMMER_SALE'
      },
      'products': [{                            // List of productFieldObjects.
        'name': 'Triblend Android T-Shirt',     // Name or ID is required.
        'id': '12345',
        'price': '15.25',
        'brand': 'Google',
        'category': 'Apparel',
        'variant': 'Gray',
        'quantity': 1,
        'coupon': ''                            // Optional fields may be omitted or set to empty string.
       },
       {
        'name': 'Donut Friday Scented T-Shirt',
        'id': '67890',
        'price': '33.75',
        'brand': 'Google',
        'category': 'Apparel',
        'variant': 'Black',
        'quantity': 1
       }]
    }
  }
});
</script>


<h1>Transaction ID Test</h1>
<p>HTTP Header: <i><?php print $sCacheHinweis?></i> (<a href="./"<?php print $sOhneCache?>>Cache deaktivieren</a><a href="?cache=1"<?php print $sMitCache?>>Cache aktivieren</a>)</p>


<br>
<br>
<table>
<tr>
    <th>Zeile</th>
    <th>Quelle</th>
    <th>Transaction ID</th>
</tr>
<tr>
    <td>1</td>
    <td><nobr>Auf dem Server generierter HTML Code</nobr></td>
    <td><?php print $iTime; ?></td>
</tr>
<tr>
    <td>2</td>
    <td><nobr>Per JavaScript generiert</nobr></td>
    <td>
        <script>
            var newTransactionID = Math.floor(Date.now() / 1000);
            document.write(newTransactionID);
        </script>
    </td>
</tr>
<tr>
    <td>3</td>
    <td><nobr>Per JavaScript aus dem localStorage</nobr></td>
    <td>
        <script>
            var lastTransactionID = null;
            if(localStorage){
                lastTransactionID = localStorage.getItem('lastTransactionID');
                localStorage.setItem('lastTransactionID',newTransactionID);
            }
            document.write(lastTransactionID);
        </script>
    </td>
</tr>

<script>
    console.log('dataLayer:',dataLayer);
</script>

</table>