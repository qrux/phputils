<?php
/**
 * Copyright (c) 2012 Troy Wu
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

// CLOG options.
define("CLOG_VERSION_LITE", 1);
define("CLOG_VERSION_DEBUG", 9);
define("CLOG_VERSION", CLOG_VERSION_LITE);
define("CLOG_MESG_BODY_WIDTH", 115);
define("CLOG_MESG_EXCEPTION_WIDTH", 50);
define("CLOG_TAB_WIDTH", 1); // Expressed as a power of two.
define("CLOG_IGNORE_DEPTH", 0);
define("CLOG_OBEY_DEPTH", 1);
define("CLOG_TIMING", true);
define("CLOG_REMOTE", true);
define("CLOG_ARRAY_KEY_FANCY", true);
define("CLOG_DEPTH_INDENT", 4);
define("CLOG_PASSWORD_PATTERN", "/(passw[o]*[r]*d|scramble)/i");

// Timer options.
define("TIMER_PREFIX_LEN", (20 + 6));
define("TIMER_TEXT_LEN", (CLOG_MESG_BODY_WIDTH - TIMER_PREFIX_LEN));
define("TIME_LIMIT_5_MINUTES", (5 * 60));

define("ONE_THOUSAND", 1000);
define("TEN_THOUSAND", (10 * ONE_THOUSAND));
define("ONE_HUNDRED_THOUSAND", (10 * TEN_THOUSAND));
define("ONE_MILLION", (ONE_THOUSAND * ONE_THOUSAND));
define("ONE_BILLION", (ONE_MILLION * ONE_THOUSAND));

define("FJ_REPORT_ERROR_VERBOSE", true);
define("FJ_ABORT_ON_ERROR", false);

// Logging constants
define("TEXT_COLOR_WHITE", "\033[1;37m");
define("TEXT_COLOR_RED", "\033[0;31m");
define("TEXT_COLOR_GREEN", "\033[0;32m");
define("TEXT_COLOR_YELLOW", "\033[1;33m");
define("TEXT_COLOR_BLUE", "\033[0;34m");
define("TEXT_COLOR_CYAN", "\033[0;36m");
define("TEXT_COLOR_ORANGE", "\033[0;33m");

define("TEXT_COLOR_BG_RED", "\033[41m");
define("TEXT_COLOR_BG_YELLOW", "\033[43m");

define("TEXT_COLOR_SUFFIX", "\033[0m");
define("TEXT_COLOR_UL_CYAN", "\033[4;36m");
define("TEXT_COLOR_UL_BLACK", "\033[4;30m");
define("TEXT_COLOR_UL_WHITE", "\033[4;37m");
define("TEXT_COLOR_UL_GREEN", "\033[4;32m");
define("TEXT_COLOR_UL_YELLOW", "\033[4;33m");
define("TEXT_COLOR_UL_RED", "\033[4;31m");

// Image handling.
define("IMAGE_API_JPEG_QUALITY", 80);
define("IMAGE_API_PNG_COMPRESSION_LEVEL", 9);
define("IMAGE_API_PNG_FILTERS", PNG_ALL_FILTERS);

define("IMAGE_EXTENSION_JPEG", ".jpg");
define("IMAGE_EXTENSION_PNG", ".png");
define("IMAGE_EXTENSION", IMAGE_EXTENSION_PNG);

// Core Variables
define("RELEASE_TYPE_DEV", 0);
define("RELEASE_TYPE_PROD", 1);
define("RELEASE_TYPE_STAGE", 8);
define("RELEASE_TYPE_BETA", 9);

// ****************************************************************
//
// NOTE - Values to be subbed at release time.
//
//   The (*)_ACTUAL defined as (*)_DEV is converted to (*)_PROD
//   by the install scripts.
//
// ****************************************************************

define("RELEASE_TYPE_ACTUAL", RELEASE_TYPE_DEV);
