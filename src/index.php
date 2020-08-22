<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=5.0">
  <title>Stencil Component Starter</title>

  <script type="module" src="/wp-content/themes/donate-test/app/donate.esm.js"></script>
  <script nomodule src="/wp-content/themes/donate-test/app/donate.js"></script>

  <?php wp_head(); ?>

  <style>
    * {
      margin: 0;
      padding: 0;
    }
    body {
      margin: 40px;
      background: #eff4f6;
    }
  </style>

</head>
<body>
  <bh-donate-form></bh-donate-form>
</body>
</html>
