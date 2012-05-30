<?php
	$block_1 = '
<h3>Main Menu</h3>
<ul class="menu">
	<li class="home active"><a href="/" title="Home Page">Home</a></li>
	<li class="contact"><a href="#/contact" title="Contact Us">Contact Us</a></li>
	<li class="news"><a href="#/news" title="Company News">News</a></li>
	<li class="links"><a href="#/links" title="Links">Links</a></li>
	<li class="license"><a href="#/license" title="License">License</a></li>
	<li class="faqs"><a href="#/faqs" title="Frequently Asked Questions">FAQs</a></li>
</ul>';

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

	$renderer = & LiquidusRenderer::getSingleton();
	$renderer->add('local-nav', $block_1);
	$renderer->add('local-nav', $block_2);
?>