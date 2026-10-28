<template>
	<div class="gs-containeer shortcodes-container">

		<div class="gs-coach-box">
			
			<div class="top-section head-section">
				<h2>{{translation('bulk-import')}}</h2>
				<p>{{translation('bulk-import-description')}}</p>
			</div>

			<hr>

			<div class="bottom-section m-t-20">

				<form class="bulk-import--form">
					
					<h2 class="m-b-15">Choose the CSV file</h2>
					<p class="m-b-20">Choose the CSV file of your Coaches you want to import.</p>

					<div class="import-upload--wrapper">
						<label for="import_file" class="import-upload btn btn-brand btn-sm"><i class="zmdi zmdi-cloud-upload"></i> Upload CSV File</label>
						<input type="file" name="import_file" id="import_file" disabled />
					</div>

					<div class="pro-block"><div class="pro-block--content"><a href="https://www.gsplugins.com/product/wordpress-coaches-plugin/pricing/?utm_source=plugin&utm_medium=shortcode-builder&utm_campaign=wordpress-coaches-plugin">Upgrade to PRO</a></div></div>

				</form>

			</div>

		</div>

	</div>
</template>

<style>

	.bulk-import--form {
		text-align: center;
		padding: 140px 0;
		position: relative;

	}

	.bulk-import--form input[type="file"] {
		display: none;
	}

	.pro-block {
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		z-index: 999;
		display: flex;
		align-items: center;
		justify-content: center;
		background: rgba(255, 255, 255, 0.8);
	}

	.pro-block--content a {
		padding: 20px 100px;
		font-size: 18px;
		box-shadow: 0px 0px 50px rgba(89, 97, 109, .1);
		border-radius: 3px;
		letter-spacing: 1px;
		background: #6472ef;
		color: #ffffff;
    	text-decoration: none;
	}

	.import-upload--wrapper {
		position: relative;
	}

	.import-upload {
		border: 1px solid #ccc;
		display: inline-block;
		padding: 6px 12px;
		cursor: pointer;
		background: #fff;
		position: relative;
		z-index: 2;
	}

</style>