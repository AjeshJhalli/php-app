<?php

function format_currency($amount) {
  return "£" . number_format((float)($amount), 2, '.', '');
}
