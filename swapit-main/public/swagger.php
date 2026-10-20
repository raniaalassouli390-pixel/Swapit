<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SwapIt API Docs</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
  <style>
    #swapit-header {
      background: #2C6E49;
      color: #fff;
      padding: 14px 24px;
      display: flex;
      align-items: center;
      gap: 14px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      box-shadow: 0 2px 6px rgba(0,0,0,.25);
    }
    #swapit-header svg {
      flex-shrink: 0;
    }
    #swapit-header .brand-title {
      font-size: 1.35rem;
      font-weight: 700;
      letter-spacing: -.3px;
    }
    #swapit-header .brand-subtitle {
      font-size: .8rem;
      opacity: .8;
      margin-top: 1px;
    }

    body  { margin: 0; background: #fafafa; }

    .swagger-ui .topbar { display: none !important; }

    .swagger-ui .btn.authorize,
    .swagger-ui .btn.execute {
      background: #2C6E49 !important;
      border-color: #2C6E49 !important;
      color: #fff !important;
    }
    .swagger-ui .btn.authorize:hover,
    .swagger-ui .btn.execute:hover {
      background: #245a3b !important;
      border-color: #245a3b !important;
    }

    .swagger-ui .opblock-summary {
      font-weight: 600;
    }

    .swagger-ui .opblock-tag {
      border-bottom: 2px solid #2C6E49 !important;
    }
    .swagger-ui .opblock-tag a {
      color: #2C6E49 !important;
    }

    .swagger-ui .info a {
      color: #2C6E49 !important;
    }

    ::-webkit-scrollbar { width: 7px; height: 7px; }
    ::-webkit-scrollbar-thumb { background: #b0bec5; border-radius: 4px; }
  </style>
</head>
<body>

  <div id="swapit-header">
    <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <circle cx="18" cy="18" r="18" fill="#245a3b"/>
      <path d="M11 14h10M11 14l3-3M11 14l3 3" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M25 22H15M25 22l-3-3M25 22l-3 3" stroke="#a8d5b5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <div>
      <div class="brand-title">SwapIt API Docs</div>
      <div class="brand-subtitle">Interactive reference for the SwapIt Marketplace REST API</div>
    </div>
  </div>

  <div id="swagger-ui"></div>

  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
  <script>
    window.onload = function () {
      SwaggerUIBundle({
        url:           "/public/swagger.json",
        dom_id:        "#swagger-ui",
        deepLinking:   true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        plugins: [
          SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout:           "StandaloneLayout",
        defaultModelsExpandDepth: 1,
        defaultModelExpandDepth:  2,
        docExpansion:     "list",
        filter:           true,
        tryItOutEnabled:  true,
        persistAuthorization: true
      });
    };
  </script>

</body>
</html>
