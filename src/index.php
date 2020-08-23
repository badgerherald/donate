<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0">
  <title>Donation Test</title>

  <script type="module" src="/wp-content/themes/donate-test/app/donate.esm.js"></script>
  <script nomodule src="/wp-content/themes/donate-test/app/donate.js"></script>

  <?php wp_head(); ?>

  <style>
    * {
      margin: 0px;
      padding: 0;
    }
    body {
      margin: 20px 20px;
      background: #eff4f6;
    }

    
    @media (min-width: 600px) {
      body {
        margin: 40px 100px;
      }
    }
  </style>

</head>
<body>
  <?php 
  echo do_shortcode( '[badgerherald_donation_form]');
  ?>
  
</body>
</html>
