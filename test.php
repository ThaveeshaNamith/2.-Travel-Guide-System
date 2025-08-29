<?php
if (isset($_GET['edit'])) {
    echo "Edit ID: " . $_GET['edit'];
} elseif (isset($_GET['delete'])) {
    echo "Delete ID: " . $_GET['delete'];
} else {
    echo "No action specified.";
}
?>