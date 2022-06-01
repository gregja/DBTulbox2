<?php

/**
 * Returns JS code to select automatically the first tab of the page
 * @return string JS code
 */
function selectFirstTab() {
    return "document.querySelector(\"[data-tab='1']\").childNodes[0].click();";
}

