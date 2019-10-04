<?php
/**
 * Copyright (c) 2012-2019 Troy Wu
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */


namespace FJ;


define("LF", "\n");
define("SLASH", DIRECTORY_SEPARATOR);
define("CRLF", "\r\n");
define("DOT", ".");
define("UNDERSCORE", "_");
define("TILDE", "~");

// JSON error-detection options.
define("JSON_DETECT_ERRORS", false);

// Time debugging options.
define("DEBUG_ANALTIME", false);
define("DEBUG_RFC3339", false);

// AJAX debugging options.
define("DEBUG_AJAX_RESPONSE", false);
define("DEBUG_AJAX_GET", false);
define("DEBUG_AJAX_POST", false);
define("DEBUG_AJAX_TIMESTAMP", false);

define("ONE_THOUSAND", 1000);
define("TEN_THOUSAND", (10 * ONE_THOUSAND));
define("ONE_HUNDRED_THOUSAND", (10 * TEN_THOUSAND));
define("ONE_MILLION", (ONE_THOUSAND * ONE_THOUSAND));
define("ONE_BILLION", (ONE_MILLION * ONE_THOUSAND));

// Image handling -- NOTE - Only use if 'gd' for PHP is installed.
//define("IMAGE_API_JPEG_QUALITY", 80);
//define("IMAGE_API_PNG_COMPRESSION_LEVEL", 9);
//define("IMAGE_API_PNG_FILTERS", PNG_ALL_FILTERS);

define("IMAGE_EXTENSION_JPEG", ".jpg");
define("IMAGE_EXTENSION_PNG", ".png");
define("IMAGE_EXTENSION", IMAGE_EXTENSION_PNG);
