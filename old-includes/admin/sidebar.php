<?php
/**
 * Sidebar
 *
 * @link  https://webberzone.com
 * @since 2.5.0
 *
 * @package    Top 10
 * @subpackage Admin/Footer
 */

?>
<div class="postbox-container">
	<div id="donatediv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_html_e( 'Support the development', 'top-10' ); ?></span></h3>
			<div class="inside" style="text-align: center">
				<div id="donate-form">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_xclick">
						<input type="hidden" name="business" value="donate@ajaydsouza.com">
						<input type="hidden" name="lc" value="IN">
						<input type="hidden" name="item_name" value="<?php esc_html_e( 'Donation for Top 10', 'top-10' ); ?>">
						<input type="hidden" name="item_number" value="tptn_plugin_settings">
						<strong><?php esc_html_e( 'Enter amount in USD', 'top-10' ); ?></strong>: <input name="amount" value="15.00" size="6" type="text"><br />
						<input type="hidden" name="currency_code" value="USD">
						<input type="hidden" name="button_subtype" value="services">
						<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
						<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php esc_html_e( 'Send your donation to the author of', 'top-10' ); ?> Top 10">
						<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
				<!-- /#donate-form -->
			</div>
			<!-- /.inside -->
	</div>
	<!-- /.postbox -->

	<div id="qlinksdiv" class="postbox meta-box-sortables">
		<h2 class='hndle metabox-holder'><span><?php esc_html_e( 'Quick links', 'top-10' ); ?></span></h3>
			<div class="inside">
				<div id="quick-links">
					<ul>
						<li>
							<a href="https://webberzone.com/plugins/top-10/">
								<?php esc_html_e( 'Top 10 plugin homepage', 'top-10' ); ?>
							</a>
						</li>
						<li>
							<a href="https://wordpress.org/plugins/top-10/faq/">
								<?php esc_html_e( 'FAQ', 'top-10' ); ?>
							</a>
						</li>
						<li>
							<a href="http://wordpress.org/support/plugin/top-10">
								<?php esc_html_e( 'Support', 'top-10' ); ?>
							</a>
						</li>
						<li>
							<a href="https://wordpress.org/support/view/plugin-reviews/top-10">
								<?php esc_html_e( 'Reviews', 'top-10' ); ?>
							</a>
						</li>
						<li>
							<a href="https://github.com/WebberZone/top-10">
								<?php esc_html_e( 'Github repository', 'top-10' ); ?>
							</a>
						</li>
						<li>
							<a href="https://webberzone.com/plugins/">
								<?php esc_html_e( 'Other plugins', 'top-10' ); ?>
							</a>
						</li>
						<li>
							<a href="https://ajaydsouza.com/">
								<?php esc_html_e( "Ajay's blog", 'top-10' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<!-- /.inside -->
	</div>
	<!-- /.postbox -->
</div>

<div class="postbox-container">
	<div id="followdiv" class="postbox meta-box-sortables">
		<h2 class='hndle'><span><?php esc_html_e( 'Follow me', 'add-to-all' ); ?></span></h3>
			<div class="inside" style="text-align: center">
				<a href="https://facebook.com/webberzone/" target="_blank"><img src="<?php echo esc_url( TOP_TEN_PLUGIN_URL . 'includes/admin/images/fb.png' ); ?>" width="100" height="100" /></a>
				<a href="https://twitter.com/webberzonewp/" target="_blank"><img src="<?php echo esc_url( TOP_TEN_PLUGIN_URL . 'includes/admin/images/twitter.jpg' ); ?>" width="100" height="100" /></a>
			</div>
			<!-- /.inside -->
	</div>
	<!-- /.postbox -->
</div>
