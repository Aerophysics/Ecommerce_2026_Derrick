<?php
// ------------------------------------------------------------
// index.php - the single entry point / home page of the app.
// It boots the shared environment (session, helpers, DB base class)
// then hands rendering to the home view. All pages follow this shape:
// a thin PHP file that includes core, then includes a view.
// ------------------------------------------------------------
require_once __DIR__ . "/core/core.php";

// The View layer renders the actual page. index.php never contains HTML
// or SQL itself - it only wires things together.
require_once __DIR__ . "/views/home.php";
