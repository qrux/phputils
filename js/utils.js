/**
 * Copyright (c) 2012 Troy Wu
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright notice,
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
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */

var console;

var isConsoleDefined = true;
try {
    console.log( "testing console..." );
} catch( ex ) {
    isConsoleDefined = false;
}

function clog( mesg ) {
    if( isConsoleDefined ) console.log( mesg );
}

/**
 * Abstracts the calling of AJAX APIs.
 *
 * @param api
 *            Name of PHP call.
 * @param postHash
 *            Data to pass through the POST - Prototype Hash object.
 * @param successHandler
 *            Function to handle a successful call - REQUIRED.
 * @param failureHandler
 *            Function to handle a failed call - OPTIONAL.
 * @param completeHandler
 *            Function to handle the completed call (success or failure) -
 *            OPTIONAL.
 * @return
 */
function api( api, postHash, successHandler, failureHandler, completeHandler ) {
    clog( "AJAX - postHash..." );
    clog( postHash );

    if( !api || !successHandler ) {
    	clog( "AJAX FASTFAIL - no method and/or successHandler; aborting." );
    	return;
    }

    $.ajax( "api/" + api + ".php", {
        data : postHash,
        type : "POST",
        dataType : "json",
        success : function( data, textStatus, jqXHR ) {
            clog( "[xhr ok] - " + api + "() - " + textStatus );

            if( data.success ) {
                clog( "[api ok] - " + api + "() - " + data.mesg );
                clog( data );
                successHandler( data.data );
            } else {
                clog( "[API ERROR!] - " + api + "() - " + data.error );
                clog( data );
                successHandler();
            }
        },
        error : function( jqXHR, textStatus, errorThrown ) {
            clog( "[XRH ERROR!] - API [ " + api + " ] - " + textStatus + " (" + errorThrown + ")" );
            if( failureHandler ) {
                failureHandler();
            }
        },
        complete : function( jqXHR, textStatus ) {
            clog( "[api] - " + api + "() completed - " + textStatus );
            if( completeHandler ) {
                completeHandler();
            }
        }
    } );
}

function shortenText( text, maxlen, showQuestionMark ) {
    if( maxlen < text.length ) {
        text = text.substr( 0, maxlen );
        var pos = text.lastIndexOf( ' ' );
        text = text.substring( 0, pos ) + "..." + (showQuestionMark ? "?" : ".");
    }

    return text;
}

function px( n ) {
    return('' + n + 'px');
}

function computeDuration( now, then ) {
    var n = now.getTime();
    var t = then.getTime();

    var secsMin = 60;
    var secsHour = 60 * secsMin;
    var secsDay = 24 * secsHour;
    var secsWeek = 7 * secsDay;
    var secsMonth = 30 * secsDay;
    var secsYear = 12 * secsMonth;

    /*
     * Number of seconds separating these two values.
     */
    var diff = (n - t) / 1000;

    var unit = "";
    var dur = 0;
    var prefix = "";

    if( diff < secsMin ) {
        return "just now";
    }

    if( diff < secsHour ) {
        unit = "min";
        dur = diff / secsMin;
    } else if( diff < secsDay ) {
        unit = "hour";
        dur = diff / secsHour;
    } else if( diff < secsWeek ) {
        unit = "day";
        dur = diff / secsDay;
    } else if( diff < secsMonth ) {
        unit = "week";
    } else {
        unit = "month";
        dur = diff / secsMonth;

        if( dur > 3 ) {
            dur = 3;
            prefix = "> ";
        }
    }

    /*
     * Make plural as necessary.
     */
    if( dur > 1 ) {
        unit = unit + "s";
    }

    dur = new Number( dur ).toFixed( 0 );

    return prefix + dur + "&nbsp;" + unit + "&nbsp;ago";
}
