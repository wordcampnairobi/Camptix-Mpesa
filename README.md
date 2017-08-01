# CampTix Payment Method: Mpesa using <a href="https://app.kopokopo.com/push_api">Kopokopo</a>

<p>Simple WordPress plugin which adds Mpesa support to CampTix</p>

<h3> Description</h3> 

<p>This plugin allows anyone whith a KopoKopo account and till number to sell tickets online using Mpesa.</p>

<h3>Installation</h3>

<h4>Minimum Requirements</h4>
<ul>
<li>WordPress 4.2 or greater</li>
<li>PHP version 5.3 or greater</li>
<li>MySQL version 5.0 or greater</li>
</ul>

<ol>
<li>Install and Setup Camptix</li>
<li>Install this plugin</li>
<li>Activate the payment method and provide Mpesa Till Number in CampTix settings panel.</li>
</ol>

Once Activated, the plugin will automatically create a Tranasaction callback url which you will use to setup a Notification URL
on KopoKopo website using the HTTP(S) POST Configuration.
