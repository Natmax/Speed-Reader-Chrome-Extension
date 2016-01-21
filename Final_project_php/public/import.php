<?php
    require("../includes/config.php");
    // This imports files into the databases.
    
    $links = array("https://ide50-nhopkins.cs50.io/data_1.txt", "https://ide50-nhopkins.cs50.io/data_1.1.txt", "https://ide50-nhopkins.cs50.io/data_1.2.txt", "https://ide50-nhopkins.cs50.io/data_1.3.txt", "https://ide50-nhopkins.cs50.io/data_1.4.txt");
    $databases = array("database.json","database.1.json","database.2.json","database.3.json","database.4.json");
    import($links[4], $databases[4]);
?>