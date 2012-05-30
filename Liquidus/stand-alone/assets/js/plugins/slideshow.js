/**
 * A script creating slideshow presentation from static content.
 *
 * HTML code structure:
 *
 * <ul id="slideshow-sample">
 *   <li>Slideshow #1</li>
 *   <li>Slideshow #2</li>
 *   <li>Slideshow #3</li>
 * </ul>
 *
 * Javascript initialization:
 *
 * <script type="text/javascript">
 *   var sampleSlideshow = new Liquidus.slideshow(document.getElementById('slideshow-sample'));
 * </script>
 *
 * @copyright   Copyright (c) 2011 Manh-Cuong Nguyen
 * @author      Manh-Cuong Nguyen [nmc] <cuongnm@f6studio.com>
 * @license     http://f6studio.com/license
 *
 * @package     Liquidus
 * @subpackage  Javascript Library
 * @link        http://f6studio.com/Liquidus
 *
 * @version     1.0.0
 */

Liquidus.slideshow = function(elm, opt) {
	this.element = typeof elm == 'string' ? document.getElementById(elm) : elm;

	if (this.element) {
		this.setOptions(opt, {
			selector: null,      // If slides is not direct children of given element, selector need to be set for retrieving slides.
			autoplay: true,      // Whether or not to play the slideshow automatically.
			delay   : 5000,      // Millisecond to wait before switching to next slide.
			control : 'top',     // Create control buttons at 'top' or 'bottom'. Set any other value to disable creation of control buttons.
			index   : 'bottom',  // Create slides index at 'top' or 'bottom'. Set any other value to disable creation of slides index.
			naming  : 'heading', // Use 'number' or 'heading' to name slides index. Set any other value to create list of empty dots.

			onBeforeInit  : null, // Function to execute before initializing slides.
			onInitSlides  : null, // Function to override slide initialization method.
			onAfterInit   : null, // Function to execute after initializing slides.

			onBeforeSwitch: null, // Function to execute before switching to next slide.
			onSwitch      : null, // Function to override slide switching method.
			onAfterSwitch : null, // Function to execute right after requested slide is switched on.

			onBeforePlay  : null, // Function to execute before playing slideshow.
			onAfterPlay   : null, // Function to execute right after slideshow is played.

			onBeforeStop  : null, // Function to execute before stopping slideshow.
			onAfterStop   : null  // Function to execute right after slideshow is stoped.
		});

		this.init() && this.options.autoplay && this.play();
	}
}

Liquidus.slideshow.prototype = {
	setOptions: function(opt, def) {
		opt = opt || {};
		this.options = {};

		// Set options.
		for (var i in def)
			this.options[i] = typeof opt[i] == 'undefined' ? def[i] : opt[i];

		return this;
	},

	addEvent: function(evt, fn) {
		if (this.options[evt] == null)
			this.options[evt] = [];
		else if (typeof this.options[evt] == 'function' && this.options[evt].constructor != Array)
			this.options[evt] = [this.options[evt]];

		// Append event handler to array of associated event handlers.
		this.options[evt].push(fn);

		return this;
	},

	fireEvent: function(evt) {
		if (this.options[evt] == null) {
			// Execute default event handler if declared.
			if (typeof this[evt] == 'function')
				this[evt].pass(Array.slice(arguments, 1), this)();
			else
				return this;
		} else {
			// Execute attached event handlers.
			if (typeof this.options[evt] == 'function' && this.options[evt].constructor != Array)
				this.options[evt].pass(Array.slice(arguments, 1), this)();
			else if (this.options[evt].constructor == Array) {
				for (var i = 0; i < this.options[evt].length; i++)
					this.options[evt][i].pass(Array.slice(arguments, 1), this)();
			}
		}

		return this;
	},

	init: function() {
		// Trigger onBeforeInit event.
		this.fireEvent('onBeforeInit');

		// Trigger onInitSlides event.
		this.fireEvent('onInitSlides');

		// Trigger onAfterInit event.
		this.fireEvent('onAfterInit');

		return this;
	},

	onBeforeInit: function() {
		this.slides  = this.options.selector != null ? this.element.getElements(this.options.selector) : this.element.getChildren();
		this.current = 0;
		this.limit   = this.slides.length;
		this.index   = null;
		this.control = null;

		// Create the slides container.
		this.container = document.createElement('div');
		this.container.className = 'slide-container';

		// Init the slideshow container.
		this.element.className = 'liquidus-slideshow';
		this.element.appendChild(this.container);

		// Create control buttons if necessary.
		if (this.options.control == 'top' || this.options.control == 'bottom') {
			this.control = document.createElement('ul');
			this.control.className = 'control-panel control-panel-' + this.options.control;

			if (this.options.control == 'top')
				this.element.insertBefore(this.control, this.container);
			else
				this.element.appendChild(this.control);

			for (var i in {'prev': '', 'play': '', 'next': ''}) {
				// Create control button.
				var btn = document.createElement('li');
				btn.className = 'button button-' + i;
				btn.innerHTML = i;

				// Inject into control panel.
				this.control.appendChild(btn);

				// Then attach onClick event handler.
				btn.onclick = function(act) {
					if (act == 'play')
						this.timer ? this.stop() : this.play();
					else if (act == 'prev')
						this.stop().switchTo(this.current - 1);
					else if (act == 'next')
						this.stop().switchTo(this.current + 1);
				}.bind(this, i);
			}
		}

		// Create slides index container if necessary.
		if (this.options.index == 'top' || this.options.index == 'bottom') {
			this.index = document.createElement('ol');
			this.index.className = 'index-panel index-panel-' + this.options.index;

			if (this.options.index == 'top')
				this.element.insertBefore(this.index, this.container);
			else
				this.element.appendChild(this.index);

			this.indexes = [];
		}
	},

	onInitSlides: function() {
		// Variable for storing the longest slide height.
		this.maxHeight = this.maxHeight || 0;

		for (var i = 0; i < this.limit; i++) {
			// Init slide.
			this.slides[i].className = 'slide slide-' + parseInt(i + 1) + (i == this.current ? ' slide-active' : '');

			// Get the longest slide height.
			if (this.slides[i].offsetHeight > this.maxHeight)
				this.maxHeight = this.slides[i].offsetHeight;

			// Move slide into slides container.
			this.container.appendChild(this.slides[i]);

			// Create slide index if necessary.
			if (this.index) {
				this.indexes[i] = document.createElement('li');
				this.indexes[i].className = 'index index-' + parseInt(i + 1) + (i == this.current ? ' index-active' : '');

				// Let's name the index.
				if (this.options.naming == 'number')
					this.indexes[i].innerHTML = parseInt(i + 1);
				else if (this.options.naming == 'heading') {
					for (var l = 1; l <= 6; l++) {
						var title = this.slides[i].getElementsByTagName('h' + l);

						if (title.length) {
							this.indexes[i].innerHTML = title[0].innerHTML;
							break;
						}
					}
				}

				// Inject slide index into index panel.
				this.index.appendChild(this.indexes[i]);

				// Then attach onClick event handler.
				this.indexes[i].onclick = function(n) { this.stop().switchTo(n); }.bind(this, i);
			}
		}
	},

	onAfterInit: function() {
		// Update the slides container's height if necessary.
		if (typeof this.maxHeight == 'number' && this.maxHeight > 0)
			this.container.setStyle('height', this.maxHeight + 'px');
	},

	switchTo: function(n) {
		if (n != this.current) {
			if (n >= this.limit)
				n = 0;
			else if (n < 0)
				n = this.limit - 1;

			// Set switching status.
			this.last    = this.current;
			this.current = n;

			// Trigger onBeforeSwitch event.
			this.fireEvent('onBeforeSwitch', this.last, this.current);

			// Trigger onSwitch event.
			this.fireEvent('onSwitch', this.last, this.current);

			// Trigger onAfterSwitch event.
			this.fireEvent('onAfterSwitch', this.last, this.current);
		}

		return this;
	},

	onSwitch: function(from, to) {
		if (window.Animator) {
			// Init animation handler.
			this.animator = this.animator ? (this.animator.stop().clearSubjects() || this.animator) : new Animator({duration: 1000});
			this.animator.options.onComplete = function(last) {
				this.slides[last].className = this.slides[last].className.replace(' slide-active', '');
			}.bind(this, from);

			// Init requested slide.
			this.slides[to].setStyle('opacity', 0);
			this.slides[to].className += ' slide-active';

			// Prepare requested slide.
			this.slides[to].getStyle('visibility') == 'visible' || this.slides[to].setStyle('visibility', 'visible');

			// Set animation subjects then play.
			this.animator.addSubject(new CSSStyleSubject(this.slides[from], 'opacity: 0'))
			             .addSubject(new CSSStyleSubject(this.slides[to], 'opacity: 1'))
			             .play();
		} else {
			// Hide all slides first.
			for (var i = 0; i < this.limit; i++)
				this.slides[i].className = this.slides[i].className.replace(' slide-active', '');

			// Then show requested slide.
			this.slides[to].className += ' slide-active';
		}
	},

	onAfterSwitch: function(from, to) {
		// Switch index if necessary.
		if (this.index) {
			this.indexes[from].className = this.indexes[from].className.replace(' index-active', '');
			this.indexes[to].className += ' index-active';
		}
	},

	play: function(n) {
		if (n && n >= this.limit)
			n = 0;

		// Trigger onBeforePlay event if necessary.
		if (typeof n == 'undefined')
			this.fireEvent('onBeforePlay');

		// Switch to scheduled slide.
		if (typeof n != 'undefined')
			this.switchTo(n);

		// Schedule next switch.
		this.timer = setTimeout(this.play.bind(this, (typeof n != 'undefined' ? n : this.current) + 1), this.options.delay);

		// Trigger onAfterPlay event if necessary.
		if (typeof n == 'undefined')
			this.fireEvent('onAfterPlay');

		return this;
	},

	onAfterPlay: function() {
		if (this.control) {
			var playBtn = this.control.firstChild.nextSibling;
			playBtn.className = playBtn.className.replace('button-play', 'button-stop');
			playBtn.innerHTML = 'stop';
		}
	},

	stop: function() {
		if (this.timer) {
			// Trigger onBeforeStop event.
			this.fireEvent('onBeforeStop');

			clearTimeout(this.timer);
			this.timer = null;

			// Trigger onAfterStop event.
			this.fireEvent('onAfterStop');
		}

		return this;
	},

	onAfterStop: function() {
		if (this.control) {
			var playBtn = this.control.firstChild.nextSibling;
			playBtn.className = playBtn.className.replace('button-stop', 'button-play');
			playBtn.innerHTML = 'play';
		}
	},
}

function ImageSlideshow(elm, opt) {
	/**
	 * HTML code structure:
	 *
	 * <ul id="image-slideshow-sample">
	 *   <li><h3>Title #1</h3><img src="Image #1" /><p>Description #1</p></li>
	 *   <li><h3>Title #2</h3><img src="Image #2" /><p>Description #2</p></li>
	 *   <li><h3>Title #3</h3><img src="Image #3" /><p>Description #3</p></li>
	 * </ul>
	 *
	 * Javascript initialization:
	 *
	 * <script type="text/javascript">
	 *   ImageSlideshow(document.getElementById('image-slideshow-sample'));
	 * </script>
	 */
	opt = opt || {};

	opt.onInitSlides = function() {
		this.maxHeight = 0;

		for (var i = 0; i < this.limit; i++) {
			var img = this.slides[i].getElement('img');
			if (img) {
				if (img.offsetHeight > this.maxHeight)
					this.maxHeight = img.offsetHeight;

				this.slides[i].setStyles({height: 'inherit', 'background-image': 'url(' + img.src + ')'});
				img.parentNode.removeChild(img);
			}
		}

		// Call the default onInitSlides event handler.
		this.onInitSlides();

		// Create the info panel.
		this.info = document.createElement('div');
		this.info.className = 'info-panel';

		// Inject the info panel into slides container.
		this.container.appendChild(this.info);
	}

	return new Liquidus.slideshow(elm, opt);
}
