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
      margin: 20px 0px;
      background: #eff4f6;
    }

.container {
 width:calc(100% * (((((var(--columnWidth) * 3) + (var(--gutterWidth) * (3 - 1 + 0)))) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter)))))) + (((var(--gutterWidth) * 0)) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter))))))));
 position:relative
}
.container,
.container * {
 --breakpointColumns:3;
 --additionalGutter:0;
 --gutterWidth:20;
 --columnWidth:80
}
.container *,
.container:after,
.container:before {
 --breakpointColumns:3;
 --additionalGutter:0
}
.container .wrapper {
 min-width:280px;
 max-width:92%;
 margin:0 auto;
 position:relative
}
@media (min-width: 620px) {
 .container {
  width:calc(100% * (((((var(--columnWidth) * 6) + (var(--gutterWidth) * (6 - 1 + 0)))) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter)))))) + (((var(--gutterWidth) * 0)) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter))))))));
  position:relative
 }
 .container .wrapper {
  min-width:580px;
  max-width:95%
 }
 .container,
 .container * {
  --breakpointColumns:6;
  --additionalGutter:0;
  --gutterWidth:20;
  --columnWidth:80
 }
 .container *,
 .container:after,
 .container:before {
  --breakpointColumns:6;
  --additionalGutter:0
 }
}
@media (min-width: 930px) {
 .container {
  width:calc(100% * (((((var(--columnWidth) * 9) + (var(--gutterWidth) * (9 - 1 + 0)))) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter)))))) + (((var(--gutterWidth) * 0)) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter))))))));
  position:relative
 }
 .container .wrapper {
  min-width:880px
 }
 .container,
 .container * {
  --breakpointColumns:9;
  --additionalGutter:0;
  --gutterWidth:20;
  --columnWidth:80
 }
 .container *,
 .container:after,
 .container:before {
  --breakpointColumns:9;
  --additionalGutter:0
 }
}
@media (min-width: 1240px) {
 .container {
  width:calc(100% * (((((var(--columnWidth) * 12) + (var(--gutterWidth) * (12 - 1 + 0)))) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter)))))) + (((var(--gutterWidth) * 0)) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter))))))));
  position:relative
 }
 .container .wrapper {
  min-width:1180px;
  max-width:90%
 }
 .container,
 .container * {
  --breakpointColumns:12;
  --additionalGutter:0;
  --gutterWidth:20;
  --columnWidth:80
 }
 .container *,
 .container:after,
 .container:before {
  --breakpointColumns:12;
  --additionalGutter:0
 }
}
@media (min-width: 1580px) {
 .container {
  width:calc(100% * (((((var(--columnWidth) * 15) + (var(--gutterWidth) * (15 - 1 + 0)))) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter)))))) + (((var(--gutterWidth) * 0)) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter))))))));
  position:relative
 }
 .container .wrapper {
  min-width:1480px;
  max-width:80%
 }
 .container,
 .container * {
  --breakpointColumns:15;
  --additionalGutter:0;
  --gutterWidth:20;
  --columnWidth:80
 }
 .container *,
 .container:after,
 .container:before {
  --breakpointColumns:15;
  --additionalGutter:0
 }
}
.container {
 margin:24px 0;
 width:100%;
 position:relative
}
.container.mobile,
.container.tablet,
.container.desktop,
.container.xl {
 display:none
}
.container.mobile {
 display:block
}
.container.black {
 background:#191919
}
@media (min-width: 930px) {
 .container.mobile,
 .container.desktop,
 .container.xl {
  display:none
 }
 .container.tablet {
  display:block
 }
}
@media (min-width: 1240px) {
 .container.mobile,
 .container.tablet,
 .container.xl {
  display:none
 }
 .container.desktop {
  display:block
 }
}
@media (min-width: 1580px) {
 .container.mobile,
 .container.tablet,
 .container.desktop {
  display:none
 }
 .container.xl {
  display:block
 }
}

.donate-form *,
.donate-form:after,
.donate-form:before {
 --breakpointColumns:3;
 --additionalGutter:2
}
@media (min-width: 620px) {
 .donate-form {
  width:calc(100% * (((((var(--columnWidth) * 6) + (var(--gutterWidth) * (6 - 1 + 0)))) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter)))))) + (((var(--gutterWidth) * 2)) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter))))))));
  position:relative;
  width:calc(100% * (((((var(--columnWidth) * 6) + (var(--gutterWidth) * (6 - 1 + 0)))) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter)))))) + (((var(--gutterWidth) * 0)) / (((var(--columnWidth) * var(--breakpointColumns)) + (var(--gutterWidth) * (var(--breakpointColumns) - 1 + var(--additionalGutter))))))))
 }
 .donate-form *,
 .donate-form:after,
 .donate-form:before {
  --breakpointColumns:6;
  --additionalGutter:2
 }
}

@media (min-width: 620px) {
  .container .wrapper .donate-form {
    position: relative;
  }
  .container .wrapper .donate-form *, .container .wrapper .donate-form:after, .container .wrapper .donate-form:before {
    --breakpointColumns: 6;
    --additionalGutter:2;
  }
}


  </style>

</head>
<body>
  <div class="container">
    <div class="wrapper">
  <?php 
  echo do_shortcode( '[badgerherald_donation_form]');
  ?>
    </div>
  </div>
  
</body>
</html>
