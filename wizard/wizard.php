<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

use sitecheckup_BillDiagnose\MemoryChecker;
use sitecheckup_BillDiagnose\ErrorChecker;
// Define the wizard steps with their names
$site_checkup_wizard_steps = [
    1 => ['function' => 'site_checkup_wizard_step1', 'name' => 'Check Memory'],
    2 => ['function' => 'site_checkup_wizard_step2', 'name' => 'Check for Errors'],
    3 => ['function' => 'site_checkup_wizard_step3', 'name' => 'Check Tables'],
    4 => ['function' => 'site_checkup_wizard_step4', 'name' => 'File Permissions'],
    5 => ['function' => 'site_checkup_wizard_step5', 'name' => 'Root Folder Extra Files'],
    // Add more steps here as needed
];
// Function to display the wizard
function site_checkup_display_wizard()
{
    global $site_checkup_wizard_steps;
    $current_step = isset($_POST['site_checkup_wizard_step']) ? intval($_POST['site_checkup_wizard_step']) : 1;
    $total_steps = count($site_checkup_wizard_steps);
?>
    <div class="site-checkup-wizard">
        <div class="site-checkup-wizard-progress">
            <div class="site-checkup-wizard-progress-bar" style="width: <?php echo esc_attr($current_step / $total_steps) * 100; ?>%;"></div>
            <div class="site-checkup-wizard-progress-text">
                <?php echo esc_attr__("Step", "site-checkup") . '&nbsp; ';
                echo esc_attr($current_step) . '&nbsp;';
                echo esc_attr__("of", "site-checkup") . '&nbsp;';
                echo esc_attr($total_steps); ?>
            </div>
        </div>
        <div class="site-checkup-wizard-buttons-top">
            <form method="post" action="">
                <?php wp_nonce_field('sitecheckup-nonce'); ?>
                <input type="hidden" name="page" value="site_checkup">
                <input type="hidden" name="tab" value="1">
                <?php if ($current_step > 1) : ?>
                    <button type="submit" name="site_checkup_wizard_step" value="<?php echo esc_attr__($current_step) - 1; ?>" id="site-checkup-prev-button" class="button button-primary"><?php esc_attr_e("Previous", "site-checkup"); ?></button>
                <?php endif; ?>
                <?php if ($current_step < $total_steps) : ?>
                    <button type="submit" name="site_checkup_wizard_step" value="<?php echo esc_attr__($current_step) + 1; ?>" id="site-checkup-next-button" class="button button-primary"><?php esc_attr_e("Next", "site-checkup"); ?></button>
                <?php endif; ?>
            </form>
        </div>
        <div class="site-checkup-wizard-steps">
            <?php
            $start_step = max(1, min($current_step - 1, $total_steps - 2));
            $end_step = min($start_step + 2, $total_steps);
            for ($i = $start_step; $i <= $end_step; $i++) :
                $class = $i == $current_step ? 'active' : ($i < $current_step ? 'completed' : '');
            ?>
                <div class="site-checkup-wizard-step <?php echo esc_attr($class); ?>">
                    <?php echo ' ' . esc_html($site_checkup_wizard_steps[$i]['name']); ?>
                </div>
            <?php endfor; ?>
        </div>
        <div class="site-checkup-wizard-content">
            <?php
            if (isset($site_checkup_wizard_steps[$current_step])) {
                call_user_func($site_checkup_wizard_steps[$current_step]['function']);
            }
            ?>
        </div>
    </div>
<?php
}
// Step 1: Check Memory
function site_checkup_wizard_step1()
{
    global $site_checkup_wizard_steps;
    $step_name = $site_checkup_wizard_steps[1]['name'];
    echo '<h2>' . esc_html__('Step 1: ', "site-checkup") . ' ' . esc_html($step_name) . '</h2>';
    // $memoryChecker = new MemoryChecker();
    if (class_exists('MemoryChecker')) {
        $memoryChecker = new MemoryChecker();
    } else {
        // Obter todas as classes declaradas
        $declaredClasses = get_declared_classes();
        $searchString = 'MemoryChecker';
        $foundClassPath = null;
        // Iterar sobre todas as classes declaradas
        foreach ($declaredClasses as $className) {
            // Verificar se o nome da classe contém a string de busca
            if (strpos($className, $searchString) !== false) {
                // Salva o caminho completo da classe
                $foundClassPath = $className;
                // Para a busca após encontrar a primeira classe correspondente
                break;
            }
        }
        // Verifica se encontramos uma classe correspondente
        if ($foundClassPath !== null) {
            //echo "Classe encontrada: {$foundClassPath}\n";
            // Verifica se a classe existe antes de instanciar
            if (class_exists($foundClassPath)) {
                // Instanciar a classe encontrada dinamicamente
                $memoryChecker = new $foundClassPath();
                // Verifica se o método 'check_memory' existe e é público
                if (method_exists($memoryChecker, 'check_memory')) {
                    // Chama o método
                    $memoryChecker->check_memory();
                } else {
                    //echo "Método 'check_memory' não encontrado na classe {$foundClassPath}.\n";
                }
            } else {
                //echo "Classe '{$foundClassPath}' não pôde ser encontrada ou carregada.\n";
            }
        } else {
            //echo "Nenhuma classe encontrada com '{$searchString}' no nome.\n";
        }
    }
?>
    <div id="modal-container-install" style="display:none; border: 2px solid #333; padding:20px;">
        <div class="modal-header">
            <h2><?php echo esc_html__('Installing... Please wait. Do not reload the page or click the back button.', 'site-checkup'); ?></h2>
        </div>
        <div class="modal-body">
            <img alt="<?php echo esc_attr__('Loading...', 'site-checkup'); ?>" src="/wp-admin/images/wpspin_light-2x.gif" id="bill_imagewait" />
        </div>
    </div>
    <?php
    $data = $memoryChecker->check_memory();
    if (is_array($data)) {
        // Check if each key exists before accessing it
        if (array_key_exists("msg_type", $data)) {
            if ($data["msg_type"] == "notok")
                echo esc_html__('Unable to retrieve memory data from your server. This could be due to a hosting issue.', "site-checkup");
        }
        if (
            array_key_exists("free", $data) &&
            array_key_exists("percent", $data)
        ) {
            // Check if free memory is less than 30MB or if the percentage of memory used is above 80%
            if ($data["free"] < 30 || $data["percent"] > 0.8) {
                // Change the color of the message to red
                $data["color"] = "color:red;";
                // Set the warning message
                $data["msg_type"] = "warning";
            }
            // Display the results
            echo esc_html__('Percentage of used memory: ', "site-checkup") .
                number_format($data["percent"] * 100, 0) .
                "%<br>";
            echo esc_html__('Free memory: ', "site-checkup") . ' ' . esc_attr($data["free"]) . "MB<br>";
        }
        //
        //
        //
        // Check if 'usage' key exists before accessing it
        if (array_key_exists("usage", $data)) {
            echo esc_html__('Memory Usage: ', "site-checkup") . ' ' . esc_attr($data["usage"]) . "MB<br>";
        }
        if (array_key_exists("limit", $data)) {
            echo esc_html__('PHP Memory Limit: ', "site-checkup") .  ' ' . esc_attr($data["limit"]) . "MB<br>";
        }
        // Check if 'wp_limit' key exists before accessing it
        if (array_key_exists("wp_limit", $data)) {
            echo esc_html__('WordPress Memory Limit: ', "site-checkup") .
                esc_attr($data["wp_limit"]) .
                "MB<br>";
        }
        // Display the status message
        echo "<br /><strong>" . esc_html__('Status: ', "site-checkup") . ' ' . "</strong>";
        if ($data["msg_type"] !== "warning") {
            echo esc_html__('All good.', "site-checkup");
            echo "<br>";
        } else {
            echo '<p style="color: red;">';
            echo esc_html__(
                "Your WordPress Memory Limit is too low, which can lead to critical issues on your site due to insufficient resources. Promptly address this issue before continuing.",
                "site-checkup"
            );
            echo "</p>";
            echo "</b>";
    ?>
            </b>
            <a href="https://wpmemory.com/fix-low-memory-limit/" target="_blank">
                <?php echo esc_html__(
                    "Learn More",
                    "site-checkup"
                ); ?>
            </a>
            </p>
            <br>
            <?php
            $all_plugins = get_plugins();
            $is_wp_memory_installed = false;
            foreach ($all_plugins as $plugin_info) {
                if ($plugin_info["Name"] === "WP Memory") {
                    $is_wp_memory_installed = true;
                    break; // Exit the loop once found
                }
            }
            if (!$is_wp_memory_installed) { ?>
                <div id="help-message">
                    <?php echo esc_html__(
                        "If you'd like help with memory management, this free plugin can help.",
                        'site-checkup'
                    ); ?>
                </div>
                <br>
                <form>
                    <a href="#" id="bill-install-plugins" site-checkup"" class="button button-primary bill-install-wpt-plugin-now">
                        <?php echo esc_html__('Install WP-Memory Free', 'site-checkup'); ?>
                    </a>
                    <input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce('sitecheckup-nonce'); ?>" />
                    <!-- Campo hidden para slug -->
                    <input type="hidden" id="slug" name="slug" value="wp-memory" />
                </form>
                <form>
            <?php }
        }
    } else {
        echo esc_html__('Unable to retrieve memory data from your server. This could be due to a hosting issue (2).', "site-checkup");
    }
}
// Step 2: Check for Errors
function site_checkup_wizard_step2()
{
    global $site_checkup_wizard_steps;
    $step_name = $site_checkup_wizard_steps[2]['name'];
    echo '<h2>' . esc_html__('Step 2:', 'site-checkup') . '&nbsp;' . esc_html($step_name) . '</h2>';
    // Obter todas as classes declaradas
    $declaredClasses = get_declared_classes();
    $searchString = 'ErrorChecker';
    $foundClassPath = null;
    // Iterar sobre todas as classes declaradas
    foreach ($declaredClasses as $className) {
        // Verificar se o nome da classe contém a string de busca
        if (strpos($className, $searchString) !== false) {
            // Salva o caminho completo da classe
            $foundClassPath = $className;
            break;
        }
    }
    // Verifica se encontramos uma classe correspondente
    if ($foundClassPath !== null) {
        if (class_exists($foundClassPath)) {
            // Instanciar a classe encontrada dinamicamente
            $errorChecker = new $foundClassPath();
        }
    }
    $errors_result = $errorChecker->bill_check_errors_today(2);
    if ($errors_result) {
            ?>
            <div id="modal-container-install" style="display:none; border: 2px solid #333; padding:20px;">
                <div class="modal-header">
                    <h2><?php echo esc_html__('Installing... Please wait. Do not reload the page or click the back button.', 'site-checkup'); ?></h2>
                </div>
                <div class="modal-body">
                    <img alt="<?php echo esc_attr__('Loading...', 'site-checkup'); ?>" src="/wp-admin/images/wpspin_light-2x.gif" id="bill_imagewait" />
                </div>
            </div>
            <?php
            echo '<p style="color: red;">';
            echo esc_html__(
                "Errors or warnings have been found in your server's error log for the last 48 hours. We recommend examining these errors and addressing them immediately to avoid potential issues, ensuring greater stability for your site.",
                'site-checkup'
            );
            echo "<br>";
            echo esc_html__(
                "Click the red button Site Errors on your admin bar",
                'site-checkup'
            );
            echo "<br />";
            echo "</p>";
            ?>
            <a href="https://wptoolsplugin.com/site-language-error-can-crash-your-site/" target="_blank">
                <?php echo esc_html__(
                    "Learn More",
                    'site-checkup'
                ); ?>
            </a>
            </p>
            <br>
            <?php
            $all_plugins = get_plugins();
            //var_dump($all_plugins);
            $is_wp_tools_installed = false;
            foreach ($all_plugins as $plugin_info) {
                if ($plugin_info["Name"] === "wptools") {
                    $is_wp_tools_installed = true;
                    break;
                }
            }
            if (!$is_wp_tools_installed) { ?>
                <div id="help-message">
                    <?php echo esc_html__(
                        "If you'd like help with errors management, this free plugin can help.",
                        'site-checkup'
                    ); ?>
                </div>
                <br>
                <form>
                    <a href="#" id="bill-install-plugins" site-checkup"" class="button button-primary bill-install-wpt-plugin-now">
                        <?php echo esc_html__('Install WPtools Free', 'site-checkup'); ?>
                    </a>
                    <input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce('sitecheckup-nonce'); ?>" />
                    <!-- Campo hidden para slug -->
                    <input type="hidden" id="slug" name="slug" value="wptools" />
                </form>
        <?php }
        } else {
            echo esc_html__(
                "No errors or warnings have been found in the last 48 hours. However, it's advisable to examine the error log for a longer time frame.",
                'site-checkup'
            );
        }
    }
    // Step 3: Additional Check
    function site_checkup_wizard_step3()
    {
        global $site_checkup_wizard_steps;
        global $wpdb;
        $step_name = $site_checkup_wizard_steps[3]['name'];
        echo '<h2>Step 3: &nbsp;' . esc_html($step_name) . '</h2>';
        // $prefix = $wpdb->prefix;
        $list_of_table = $wpdb->get_results("SHOW TABLE STATUS");
        global $wpdb;
        echo "<br><big>";
        echo esc_html__(
            "Check the status of all your tables. Everything should be marked as OK.",
            "site-checkup"
        );
        echo "<br></big>";
        $error_found = false;
        ?>
        <br>
        <table class="site-checkup_admin_table">
            <tr>
                <th style="width:50px;"><strong><?php esc_attr_e(
                                                    "Status",
                                                    "site-checkup"
                                                ); ?></strong></th>
                <th><strong><?php esc_attr_e("Table Name", "site-checkup"); ?></strong></th>
                <th><strong><?php esc_attr_e("Engine", "site-checkup"); ?></strong></th>
                <th><strong><?php esc_attr_e("Last Update", "site-checkup"); ?></strong></th>
                <th><strong><?php esc_attr_e(
                                "Data Length (Aproximate)",
                                "site-checkup"
                            ); ?></strong></th>
            </tr>
            <?php foreach ($list_of_table as $check) { ?>
                <tr>
                    <td>
                        <?php
                        $table_name = preg_replace(
                            "/[&<>=#\(\)\[\]\{\}\?\"\' ]/",
                            "",
                            $check->Name
                        );
                        $table_name = trim($table_name);
                        $query_result = $wpdb->get_results("CHECK TABLE `" . $table_name . "`");
                        foreach ($query_result as $row) {
                            if ($row->Msg_text) {
                                echo esc_attr($row->Msg_text);
                                if ($row->Msg_text !== 'OK')
                                    $error_found = true;
                            }
                        }
                        ?>
                    </td>
                    <td><?php echo esc_attr($check->Name); ?></td>
                    <td><?php echo esc_attr($check->Engine); ?></td>
                    <td><?php if (!empty($check->Update_time)) {
                            echo esc_attr($check->Update_time);
                        } else {
                            echo esc_attr($check->Create_time);
                        } ?></td>
                    <td><?php echo esc_attr($check->Data_length); ?></td>
                </tr>
            <?php } ?>
        </table>
        <hr>
        <h4>
            <?php
            if ($error_found) {
                echo '<span style="color: red;">';
                esc_attr_e(
                    "There are issues with your tables.",
                    "site-checkup"
                );
                echo '</span>'; ?>
                <a href="https://wptoolsplugin.com/optimize-and-repair-innodb-and-myisam-database-tables/" target="_blank">
                    <?php echo esc_attr__(
                        "Learn More",
                        'site-checkup'
                    ); ?>
                </a>
            <?php
            }
        }
        // Step 4: Additional Check
        function site_checkup_wizard_step4()
        {
            global $site_checkup_wizard_steps;
            $step_name = $site_checkup_wizard_steps[4]['name'];
            echo '<h2>Step 4: &nbsp;' . esc_html($step_name) . '</h2>';
            echo "<h2>" .
                esc_attr(__("Permission Scheme for WordPress", "site-checkup")) .
                "</h2>";
            echo esc_attr(__("Typically", "site-checkup"));
            echo ":";
            echo "<br>";
            echo esc_attr(__("Files", "site-checkup"));
            echo ": 644";
            echo "<br>";
            echo esc_attr(__("Folders", "site-checkup"));
            echo ": 755";
            echo "<br>";
            echo esc_attr(__("File wp-config.php: 660", "site-checkup"));
            echo "<br>";
            echo "<br>";
            // echo ABSPATH.'wp-config.php';
            if (file_exists(ABSPATH . "wp-config.php")) {
                echo esc_attr_e("wp-config.php currently permissions:", "site-checkup") .
                    esc_attr(decoct(fileperms(ABSPATH . "wp-config.php") & 0777));
            }
            echo "<br>";
            echo "<br>";
            echo '<a href="https://wptoolsplugin.com/wordpress-file-permissions/" >';
            echo esc_attr(__("Visit plugin's site for detais", "site-checkup")) . ".";
            echo "</a>";
            echo "<br>";
            echo "<br>";
            echo "<br>";
            $files = site_checkup_fetch_files(ABSPATH);
            if ($files === false) {
                echo "<h3>" . esc_attr(__("Unable to read files", "site-checkup")) . "</h3>";
                return;
            }
            ?>
            <table class="site-checkup_admin_table" align="center">
                <thead>
                    <th><?php echo esc_attr(__("Permissions", "site-checkup")); ?></th>
                    <th><?php echo esc_attr(__("Files / Folders", "site-checkup")); ?></th>
                </thead>
                <?php
                $ctdf = 0;
                $ctdd = 0;
                for ($i = 0; $i < count($files); $i++) {
                    if (is_dir($files[$i])) {
                        if ($files[$i] == "wp-config.php") {
                            $ctdd++;
                            continue;
                        }
                        if (decoct(fileperms($files[$i]) & 0777) != "755") {
                            $ctdd++;
                            if ($ctdd < 51) {
                                echo "<tr>";
                                echo "<td>";
                                echo esc_attr(decoct(fileperms($files[$i]) & 0777));
                                echo "</td>";
                                echo "<td>";
                                echo esc_attr($files[$i]);
                                echo "</td>";
                                echo "<tr>";
                            }
                        }
                    } else {
                        if (@decoct(fileperms($files[$i]) & 0777) != "644") {
                            $ctdf++;
                            if ($ctdf < 51) {
                                echo "<tr>";
                                echo "<td>";
                                try {
                                    echo esc_attr(@decoct(fileperms($files[$i]) & 0777));
                                } catch (exception $e) {
                                }
                                // echo decoct(fileperms($files[$i]) & 0777);
                                echo "</td>";
                                echo "<td>";
                                echo esc_attr($files[$i]);
                                echo "</td>";
                                echo "<tr>";
                            }
                        }
                    }
                }
                ?>
            </table>
            <?php
            echo "<br>";
            echo "<br>";
            if ($ctdf > 0) {
                echo esc_attr($ctdf) .
                    " " .
                    esc_attr__(
                        "Files with wrong permissions. This plugin will show max 50.",
                        "site-checkup"
                    );
            } else {
                echo esc_attr(__("No files found with wrong permissions.", "site-checkup"));
            }
            echo "<br>";
            echo "<br>";
            if ($ctdd > 0) {
                echo esc_attr($ctdd) .
                    " " .
                    esc_attr(
                        esc_attr__(
                            "Folders with wrong permissions. This plugin will show max 50.",
                            "site-checkup"
                        )
                    );
            } else {
                echo esc_attr(__("No folders found with wrong permissions.", "site-checkup"));
            }
            echo "<br>";
        }
        // Step 5: Additional Check
        function site_checkup_wizard_step5()
        {
            global $site_checkup_wizard_steps;
            $step_name = $site_checkup_wizard_steps[5]['name'];
            echo '<h2>Step 5: &nbsp;' . esc_html($step_name) . '</h2>';
            echo "<h2>" . esc_attr__('By security.', 'site-checkup') . "</h2>";
            $site_checkup_report_files = array();
            $path = ABSPATH;
            $whitelist = array(
                '.htaccess',
                '403.shtml',
                'BingSiteAuth.xml',
                'favicon.ico',
                'index.php',
                'license.txt',
                'readme.html',
                'robots.txt',
                'sitemap.xml',
                'wp-activate.php',
                'wp-blog-header.php',
                'wp-comments-post.php',
                'wp-config-sample.php',
                'wp-config.php',
                'wp-cron.php',
                'wp-links-opml.php',
                'wp-load.php',
                'wp-login.php',
                'wp-mail.php',
                'wp-settings.php',
                'wp-signup.php',
                'wp-trackback.php',
                'xmlrpc.php'
            );
            // Check directory exists or not
            if (file_exists($path) && is_dir($path)) {
                // Scan the files in this directory
                $result = scandir($path);
                // Filter out the current (.) and parent (..) directories
                $files = array_diff($result, array('.', '..'));
                if (count($files) > 0) {
                    // Loop through retuned array
                    foreach ($files as $file) {
                        if (is_file("$path/$file")) {
                            if (in_array($file, $whitelist))
                                continue;
                            $site_checkup_report_files[] = $file;
                        } else if (is_dir("$path/$file")) {
                            // Recursively call the function if directories found
                            // outputFiles("$path/$file");
                        }
                    }
                }
            } else {
                echo esc_attr__('ERROR on Search the root folder. Maybe no Hosting permissions.', 'site-checkup');
            }
            if (count($site_checkup_report_files) > 0) {
                echo esc_attr__('File(s) found on site root folder', 'site-checkup') . ':';
                echo '<br>';
                for ($i = 0; $i < count($site_checkup_report_files); $i++) {
                    if ($i > 19)
                        continue;
                    echo esc_attr($site_checkup_report_files[$i]);
                    echo '<br>';
                }
                if ($i > 19) {
                    echo '<br>';
                    echo esc_attr__('More files found', 'site-checkup') . '...';
                }
                echo '<br>';
                echo '<br>';
                echo '<span style="color: red;">';
                echo esc_attr__("Make sure the files in the root directory of your site are supposed to be there. If they aren’t needed, consider removing them to keep things secure.", "site-checkup");
                echo "</span>";
            } else
                echo esc_attr__('No extra files found! All Right.', 'site-checkup') . '...';
        }
        // Function to handle the Wizard tab
        function site_checkup_tab_wizard($active_tab)
        {
            global $site_checkup_label_tabs;
            site_checkup_render_nav_tabs($active_tab);
            ?>
            <div class="site-checkup-content">
                <h2><?php echo esc_html($site_checkup_label_tabs[$active_tab]); ?></h2>
                <?php
                if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'sitecheckup-nonce')) {
                    site_checkup_display_wizard();
                } else {
                    // Initial wizard display or if nonce fails
                    $_POST['site_checkup_wizard_step'] = 1;
                    site_checkup_display_wizard();
                }
                ?>
            </div>
        <?php
        }
        // Enqueue necessary scripts and styles
        function site_checkup_enqueue_wizard_scripts()
        {
            wp_enqueue_style('site-checkup-wizard-style', SITECHECKUPURL . 'assets/css/site-checkup-wizard-style.css');
            //wp_enqueue_script('site-checkup-wizard-script', plugin_dir_url(__FILE__) . 'js/site-checkup-wizard-script.js', array('jquery'), '1.0', true);
        }
        add_action('admin_enqueue_scripts', 'site_checkup_enqueue_wizard_scripts');
        function site_checkup_fetch_files($dir)
        {
            try {
                $x = scandir($dir);
            } catch (exception $e) {
                return false;
            }
            $result = [];
            foreach ($x as $filename) {
                if ($filename == ".") {
                    continue;
                }
                if ($filename == "..") {
                    continue;
                }
                $result[] = $dir . $filename;
                $filePath = $dir . $filename;
                if (is_dir($filePath)) {
                    $filePath = $dir . $filename . "/";
                    foreach (site_checkup_fetch_files($filePath) as $childFilename) {
                        $result[] = $childFilename;
                    }
                }
            }
            return $result;
        }
