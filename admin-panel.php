<?php
// TODO Use Setting API

// create custom plugin settings menu
add_action('admin_menu', 'dvwei_admin_Page');
function dvwei_admin_Page() {
	add_menu_page(WPWEI_NAME, WPWEI_NAME, 'administrator', WPWEI_FILE, 'dvwei_settings_page_output' , 'dashicons-migrate' );
}

function dvwei_settings_page_output() { ?>

	<div class="wrap" id="wpsc_admin_wrap">

		<h2><?php echo WPWEI_NAME; ?></h2>

		<?php settings_errors(); ?>

		<div class="wrap">

			<div class="admin_contents" id="dvwooexportimport">

				<form method="post" action="options.php?page=<?php echo WPWEI_FILE; ?>" id="dvwooexportimport_form" enctype="multipart/form-data">

					<h3>Export Data</h3>
					<a href="admin.php?page=<?php echo WPWEI_FILE ?>&dvwooei_action=export" class="button">Export Data</a>
					<br /><br />

					<h3>Import Data</h3>
					<input type="file" name="imported_file" /><br /><br />
					<input type="submit" name="submit" id="submit" class="button button-primary" value="Import">

				</form>

			</div>

		</div>

	</div>
<?php }
