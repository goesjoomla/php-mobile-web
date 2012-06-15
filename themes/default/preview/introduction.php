<?php
	$slide_1 = '
<h2>Next generation web template system</h2>
<h3>A better way to adapt website for difference platform gracefully</h3>
<p>
	<img src="'.Liquidus::getWebPath(dirname(dirname(__FILE__)).DS.'img'.DS.'intro-img.png').'" />
	Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed am ut labore et dolore magna aliquyam.
	<a href="#/introduction" title="Click to learn more">Learn more...</a>
</p>';
/*
	$block_2 = new stdClass();
	$block_2->title   = 'More Menu';
	$block_2->content = '
<ul class="menu">
	<li class="home active"><a href="/" title="Home Page">Home</a></li>
	<li class="contact"><a href="#/contact" title="Contact Us">Contact Us</a></li>
	<li class="news"><a href="#/news" title="Company News">News</a></li>
	<li class="links"><a href="#/links" title="Links">Links</a></li>
	<li class="license"><a href="#/license" title="License">License</a></li>
	<li class="faqs"><a href="#/faqs" title="Frequently Asked Questions">FAQs</a></li>
</ul>';
	$block_2->classSuffix = '-black';
*/
	$renderer = & LiquidusRenderer::getSingleton();
	$renderer->add('introduction', $slide_1);
//	$renderer->add('introduction', $block_2);
?>