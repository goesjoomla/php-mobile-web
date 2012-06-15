/**
 * F6 Liquidus's base Javascript library.
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

var Liquidus = {
	// Cookie handler.
	cookie: function(name, value, days) {
		if (arguments.length == 1) {
			// Read cookie.
			name = name + '=';

			var ca = document.cookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ')
					c = c.substring(1, c.length);

				if (c.indexOf(name) == 0)
					return c.substring(name.length, c.length); // Return cookie data.
			}

			return null;
		} else {
			// Write cookie.
			var expires = '';
			if (typeof days != 'undefined') {
				var date = new Date();
				date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
				expires = '; expires=' + date.toGMTString();
			}

			document.cookie = name + '=' + value + expires + '; path=/';
		}
	},

	// Side-by-side columns height equalizer.
	equalHeight: function(cols) {
		// Get longest column.
		var max = 0;
		for (var i = 0; i < cols.length; i++) {
			cols[i] = typeof cols[i] == 'string' ? dojo.query(cols[i])[0] : cols[i];
			if (cols[i] && max < cols[i].offsetHeight)
				max = cols[i].offsetHeight;
		}

		for (var i = 0; i < cols.length; i++) {
			if (cols[i] && cols[i].offsetHeight < max) {
				// Update column height to equal to the longest column.
				var div = dojo.query('div.module', cols[i]);
				if (!div.length)
					dojo.setStyle(cols[i], 'height', max + 'px');
				else {
					div = div[div.length - 1];
					dojo.setStyle(div, 'height', parseInt(div.offsetHeight + (max - cols[i].offsetHeight) - dojo.getStyle(div, 'paddingTop') - dojo.getStyle(div, 'paddingBottom')) + 'px');

					// Fine-tune border-radius fail-over for IE.
					if (window.ActiveXObject) {
						var borderRadiusFix = dojo.query('div.border-radius-bottom', div);
						if (borderRadiusFix)
							dojo.setStyle(borderRadiusFix, 'borderTop', parseInt((div.offsetTop + div.offsetHeight) - (borderRadiusFix.offsetTop + borderRadiusFix.offsetHeight)) + 'px solid ' + dojo.getStyle(div, 'backgroundColor'));
					}
				}
			}
		}
	},

	// Font resizer.
	fontResizer: function(act) {
		var fs, cfs = parseInt(dojo.getStyle(dojo.query('body')[0], 'fontSize'));

		if (act == '+')
			fs = cfs + Liquidus.fontResizer.step;
		else if (act == '-')
			fs = cfs - Liquidus.fontResizer.step;
		else
			fs = Liquidus.fontResizer.normal;

		if (fs >= Liquidus.fontResizer.min && fs <= Liquidus.fontResizer.max)
			dojo.setStyle(dojo.query('body')[0], 'fontSize', fs + 'px');
	},

	// Simple method to check if mouse is really out.
	mouseReallyOut: function(src, evt) {
		evt = evt || window.event;

		var tg = evt.relatedTarget || evt.toElement;
		while (tg && tg != src && tg.nodeName != 'BODY')
			tg = tg.parentNode;

		return tg == src ? false : true;
	},

	// Simple method for setting options.
	setOptions: function(opt, def) {
		// Set options.
		opt = opt || {};
		for (var i in def)
			opt[i] = typeof opt[i] == 'undefined' ? def[i] : opt[i];

		return opt;
	}
}

// Declare drop-down menu, with optional drop-line, handler.
dojo.declare('Liquidus.dropMenu', null, {
	constructor: function(menu, options) {
		// Query for menu element.
		if (!(this.menu = typeof menu == 'string' ? dojo.query(menu)[0] : menu))
			return;

		// Set options.
		this.options = Liquidus.setOptions(options, {
			hideDelay: 500,                  // Delay for hiding sub-menu.
			dropLine: 'drop-line',           // Drop-line menu CSS class.
			centralize: true,                // Centralize drop-line menu.
			container: this.menu.parentNode, // Element for centralizing drop-line menu relatively to.
			bounceBack: null                 // Centralization's right edge limitation (element).
		});

		// Is this a drop-line menu.
		this.isDropLine = dojo.hasClass(this.menu, this.options.dropLine);
		this.centralize = (this.isDropLine && this.options.centralize);

		if (this.centralize) {
			// Preparation for centralization.
			this.options.container = typeof this.options.container == 'string' ? dojo.query(this.options.container)[0] : this.options.container;
			this.options.bounceBack = typeof this.options.bounceBack == 'string' ? dojo.query(this.options.bounceBack)[0] : this.options.bounceBack;

			// Get active menu items.
			this.active = dojo.query('> li.active', this.menu);

			// Centralize sub-menu of active root menu item.
			this.active.forEach(this.centralizeSub);
		}

		// Set event handlers.
		dojo.query('li', this.menu).forEach(dojo.hitch(this, function(e) {
			dojo.connect(e, 'onmouseenter', dojo.hitch(this, function(e, evt) {
				// Hide active menu item.
				if (this.isDropLine && dojo.hasClass(e, 'parent'))
					this.toggleActive(true);

				// Clear delayed hide timeout.
				this.timer && clearTimeout(this.timer);
				this.timer = null;

				// Toggle this menu item's hover state.
				this.toggleHover(e);

				// Centralize visible sub-menu.
				if (this.centralize)
					this.centralizeSub(e);

				// Reposition sub-menu if necessary.
				this.repositionSub(e, evt);
			}, e));

			dojo.connect(e, 'onmouseleave', dojo.hitch(this, function(e, evt) {
				if (!Liquidus.mouseReallyOut(e, evt))
					return;

				if (dojo.hasClass(e, 'parent')) {
					// Set delayed hide timeout.
					this.timer && clearTimeout(this.timer);
					this.timer = setTimeout(dojo.hitch(this, function(hovering) { this.hideMenu(hovering); }, e), this.options.hideDelay);
				} else
					this.hideMenu(e);
			}, e));
		}));
	},

	// Toggle menu item's hover state.
	toggleHover: function(e, disable) {
		if (disable && dojo.hasClass(e, 'mouseover')) {
			if (dojo.hasClass(e, 'parent')) {
				// Disable hover state of all children also.
				var sub;
				if (sub = dojo.query('> ul', e)[0])
					dojo.query('> li.mouseover', sub).forEach(dojo.hitch(this, function(hovering) { this.hideMenu(hovering); }));
			}

			dojo.removeClass(e, 'mouseover');
		} else if (!disable && !dojo.hasClass(e, 'mouseover')) {
			// Disable hover state of all same level items.
			dojo.query('> li.mouseover', e.parentNode).forEach(dojo.hitch(this, function(hovering) { this.hideMenu(hovering); }));

			dojo.addClass(e, 'mouseover');
		}
	},

	// Toggle menu item's active state.
	toggleActive: function(hide) {
		if (hide || !dojo.query('li.mouseover', this.menu).length)
			this.active.forEach(function(e) { hide ? dojo.removeClass(e, 'active') : dojo.addClass(e, 'active'); });
	},

	// Hide a visible sub-menu.
	hideMenu: function(e) {
		// Toggle menu item's hover state.
		this.toggleHover(e, true);

		// Unhide active menu item.
		if (this.isDropLine && dojo.hasClass(e, 'parent'))
			this.toggleActive();
	},

	// Centralize second-level sub-menu of a drop-line menu.
	centralizeSub: function(e) {
		var sub;
		if (!e.centralized && e.parentNode == this.menu && dojo.hasClass(e, 'parent') && (sub = dojo.query('> ul', e)[0])) {
			// Calculate sub-menu width.
			var subWidth = 2; // Fix possible text-wrapping bug.
			dojo.query('> li', sub).forEach(function(child) {
				subWidth += child.offsetWidth + dojo.getStyle(child, 'marginLeft') + dojo.getStyle(child, 'marginRight');
			});
			dojo.setStyle(sub, 'width', subWidth + 'px');

			// Prepare variables.
			var container = this.options.container,
			bounceBack = this.options.bounceBack,

			leftDiff = parseInt((subWidth - e.offsetWidth) / 2),
			rightLimit = dojo.position(container).x + container.offsetWidth - (bounceBack ? bounceBack.offsetWidth : 0),

			menuPos = dojo.position(this.menu),
			thisPos = dojo.position(e);

			// Fine-tune left edge difference.
			if (thisPos.x - leftDiff < menuPos.x)
				leftDiff = thisPos.x - menuPos.x;
			else if (thisPos.x - leftDiff + subWidth > rightLimit)
				leftDiff = thisPos.x - (rightLimit - subWidth);

			// Centralize now.
			dojo.setStyle(sub, 'marginLeft', (thisPos.x - dojo.position(sub).x - leftDiff) + 'px');

			e.centralized = true;
		}
	},

	// Reposition a sub-menu if it is rendered out of view port.
	repositionSub: function(e, evt) {
		var sub;
		if (!e.centralized && dojo.hasClass(e, 'parent') && (sub = dojo.query('> ul', e)[0])) {
			// Backup margin-left value of sub-menu and view port size.
			this.winSize = typeof this.winSize == 'undefined' ? dojo.window.getBox() : this.winSize;
			this.docSize = typeof this.docSize == 'undefined' ? dojo.coords(dojo.query('body')[0]) : this.docSize;
			sub._marginLeft = typeof sub._marginLeft == 'undefined' ? dojo.getStyle(sub, 'marginLeft') : sub._marginLeft;

			// Get current view port size.
			var winSize = dojo.window.getBox(), docSize = dojo.coords(dojo.query('body')[0]);

			// Reset margin-left value of sub-menu if view port size changed.
			if (winSize.w != this.winSize.w || docSize.w != this.docSize.w || sub._marginLeft != dojo.getStyle(sub, 'marginLeft'))
				dojo.setStyle(sub, 'marginLeft', sub._marginLeft + 'px');

			// Reposition sub-menu if necessary.
			var subBox = dojo.position(sub), limit = winSize.w < docSize.w ? winSize.w - 17 : docSize.w;
			if (subBox.x < 0)
				(subBox._fix = 'left') && dojo.setStyle(sub, 'marginLeft', (sub._marginLeft - subBox.x) + 'px');
			else if (subBox.x + subBox.w > limit)
				(subBox._fix = 'right') && dojo.setStyle(sub, 'marginLeft', (sub._marginLeft - ((subBox.x + subBox.w) - limit)) + 'px');

			if (dojo.position(sub).x + dojo.position(sub).w == dojo.position(e.parentNode).x + dojo.position(e.parentNode).w)
				dojo.setStyle(sub, 'marginLeft', (dojo.getStyle(sub, 'marginLeft') + (subBox._fix == 'right' ? evt.clientX - (dojo.position(sub).x + dojo.position(sub).w) : evt.clientX)) + 'px');
		}
	}
});

// Declare floating box handler.
dojo.declare('Liquidus.floatingBox', null, {
	constructor: function(box, options) {
		// Query for floating box element.
		if (!(this.box = typeof box == 'string' ? dojo.query(box)[0] : box))
			return;

		// Set options.
		this.options = Liquidus.setOptions(options, {
			top: null,     // Box's absolute top position.
			right: null,   // Box's absolute right position.
			bottom: null,  // Box's absolute bottom position.
			left: null,    // Box's absolute left position.
			delay: 500,    // Delay before scrolling box into view.
			minWidth: null // Minimum required view port width.
		});

		// Switch box position type to absolute.
		dojo.getStyle(this.box, 'position') == 'absolute' || dojo.setStyle(this.box, 'position', 'absolute');

		// Preset box position.
		this.setPos(this.box, options);

		// Hide box if view port width does not fulfill minimum required one.
		if (this.options.minWidth && dojo.window.getBox().w - 17 < this.options.minWidth)
			dojo.setStyle(this.box, 'display', 'none');

		// Set event handlers.
		dojo.connect(window, 'onscroll', this, 'repositionBox');

		// IE7 fail-over.
		if (window.ActiveXObject && window.XMLHttpRequest && !document.querySelectorAll)
			dojo.connect(window, 'onresize', dojo.hitch(this, function() { setTimeout(dojo.hitch(this, 'repositionBox'), 500); }));
		else
			dojo.connect(window, 'onresize', this, 'repositionBox');

		this.lastWinSize = dojo.window.getBox();
	},

	// Set floating box position.
	setPos: function() {
		// Preset box position.
		for (var i in {top: '', right: '', bottom: '', left: ''}) {
			// Reset box position.
			typeof this.box.style[i] == 'undefined' || dojo.setStyle(this.box, i, 'auto');

			// Set box position.
			var val;
			if (typeof this.options[i] == 'number')
				val = this.options[i];
			else if (typeof this.options[i] == 'function')
				val = parseInt(this.options[i]());
			else if (typeof this.options[i] == 'string')
				val = parseInt(eval(this.options[i]));

			if (typeof val != 'undefined') {
				if (i == 'top')
					val = val + dojo.window.getBox().t;
				else if (i == 'bottom')
					val = val - dojo.window.getBox().t;

				dojo.setStyle(this.box, i, val + 'px');
			}
		}

		// Store original top position.
		this.box._top = dojo.position(this.box).y;
	},

	// Scroll floating box into view.
	repositionBox: function() {
		var winSize = dojo.window.getBox();

		// Hide box if minimum required width does not fulfilled.
		if (this.options.minWidth && winSize.w - 17 < this.options.minWidth)
			return dojo.setStyle(this.box, 'display', 'none');

		// Otherwise, unhide box if it is currently hidden.
		dojo.getStyle(this.box, 'display') != 'none' || dojo.setStyle(this.box, 'display', 'block');

		// Update necessary variables if window is resized.
		if (this.lastWinSize.w != winSize.w || this.lastWinSize.h != winSize.h) {
			// Reset box position if it is not floating-disabled.
			this.setPos();

			// IE6 fail-over.
			if (window.ActiveXObject && !window.XMLHttpRequest)
				this.box._top = this.box._top - winSize.t - (this.lastWinSize.h - winSize.h);

			this.lastWinSize = winSize;
		}

		// Calculate new top position.
		var top = this.box._top + winSize.t, cur = dojo.position(this.box).y;
		if (top != cur) {
			this.options.delay && this.timer && clearTimeout(this.timer);
			this.timer = null;

			// Set animation.
			this.animator && this.animator.stop();
			this.animator = dojo.animateProperty({
				node: this.box,
				duration: 1000,
				easing: dojo.fx.easing.bounceOut,
				properties: {top: top},
				onBegin: dojo.hitch(this, function() {
					dojo.getStyle(this.box, 'bottom') == 'auto' || dojo.setStyle(this.box, 'bottom', 'auto');
				}),
				onEnd: dojo.hitch(this, function() {
					// IE8 fail-over.
					if (window.ActiveXObject && document.querySelectorAll) {
						dojo.setStyle(this.box, 'visibility', 'hidden');
						dojo.setStyle(this.box, 'visibility', 'visible');
					}
				})
			});

			if (this.options.delay)
				this.timer = setTimeout(dojo.hitch(this, function() { this.animator.play(); }), this.options.delay);
			else
				this.animator.play();
		}
	}
});

// Declare sticky box handler.
dojo.declare('Liquidus.stickyBox', null, {
	constructor: function(box, options) {
		// Query for sticky box element.
		if (!(this.box = typeof box == 'string' ? dojo.query(box)[0] : box))
			return;

		// Set options.
		this.options = Liquidus.setOptions(options, {
			limit: this.box.parentNode, // Limit sticky box's top and bottom position to this element's dimension.
			fixed: true                 // Use fixed or absolute position type.
		});

		// Initialization.
		this.options.limit = typeof this.options.limit == 'string' ? dojo.query(this.options.limit)[0] : this.options.limit;

		this.oriStyle = {position: dojo.getStyle(this.box, 'position'), top: dojo.getStyle(this.box, 'top'), left: dojo.getStyle(this.box, 'left')};
		this.oriPos = dojo.position(this.box);
		this.posLimit = dojo.position(this.options.limit);

		// Do not continue if box is not shorter than limitation.
		if (this.oriPos.y + this.box.offsetHeight >= this.posLimit.y + this.options.limit.offsetHeight)
			return;

		// IE6 fail-over.
		if (window.ActiveXObject && !window.XMLHttpRequest)
			this.options.fixed = false;

		// Prevent reload of flash object when changing its parent's position type under FireFox (see http://flowplayer.org/forum/4/16372).
		//if ((document.getBoxObjectFor != null || window.mozInnerScreenX != null) && dojo.query('object', this.box)[0])
		//	this.options.fixed = false;

		this.setPos();

		// Set event handlers.
		dojo.connect(window, 'onscroll', dojo.hitch(this, 'repositionBox'));
		dojo.connect(window, 'onresize', dojo.hitch(this, 'repositionBox'));

		this.lastWinSize = dojo.window.getBox();
	},

	// Set sticky box position.
	setPos: function() {
		var parPos, win = dojo.window.getBox();

		// Check if box's parent is positioned relatively.
		this.alter = (parPos = dojo.getStyle(this.box.parentNode, 'position')) == 'relative' ? dojo.position(this.box.parentNode) : {x: 0, y: 0};
		parPos != 'relative' || (this.alter.y += win.t);

		// Switch box position to absolute.
		dojo.setStyle(this.box, {
			position: 'absolute',
			top: ((parPos == 'relative' ? 0 : win.t) + (this.oriPos.y - this.alter.y)) + 'px',
			left: (this.oriPos.x - dojo.getStyle(this.box, 'marginLeft') - this.alter.x) + 'px'
		});

		// Then fix position for element that is positioned relatively to box.
		var fix = dojo.query('+ *', this.box)[0];
		if (fix && dojo.getStyle(fix, 'position') == 'relative' && (fix._left = dojo.getStyle(fix, 'left')) != 'auto')
			dojo.setStyle(fix, 'left', (dojo.getStyle(fix, 'left') + this.box.offsetWidth + dojo.getStyle(this.box, 'marginLeft') + dojo.getStyle(this.box, 'marginRight')) + 'px');
	},

	// Reposition sticky box.
	repositionBox: function() {
		var win = dojo.window.getBox(), limit = this.options.limit, fixed = this.options.fixed;

		// Check if window is resized.
		if (this.lastWinSize.w != win.w || this.lastWinSize.h != win.h) {
			// Backup current sticky state.
			var _current = {
				position: dojo.getStyle(this.box, 'position'),
				top: dojo.getStyle(this.box, 'top')
			};

			// Update original position.
			dojo.setStyle(this.box, {
				position: this.oriStyle.position,
				top: this.oriStyle.top + 'px',
				left: this.oriStyle.left + 'px'
			});

			this.oriPos.x = dojo.position(this.box).x;
			this.posLimit.x = dojo.position(limit).x;

			var fix = dojo.query('+ *', this.box)[0];
			if (fix && fix._left)
				dojo.setStyle(fix, 'left', fix._left + 'px');

			this.setPos();

			// Restore sticky state.
			dojo.setStyle(this.box, {
				position: _current.position,
				top: _current.top + 'px'
			});

			this.lastWinSize = win;
		}

		// Detect scrolling state.
		this.lastWinScrollTop = this.lastWinScrollTop || 0;
		this.scrolledDistance = win.t == this.lastWinScrollTop ? this.scrolledDistance : win.t - this.lastWinScrollTop;
		var scrollingDir = this.scrolledDistance > 0 ? 'down' : 'up';

		var pos = dojo.getStyle(this.box, 'position'), top = dojo.getStyle(this.box, 'top'), y;

		// Update box position type if scrolling direction changed.
		if (fixed && pos == 'fixed' && this.lastScrollingDir && scrollingDir != this.lastScrollingDir) {
			pos = 'absolute';
			top = (win.t + top - this.alter.y);

			dojo.setStyle(this.box, {
				position: pos,
				top: top + 'px',
				left: (this.oriPos.x - dojo.getStyle(this.box, 'marginLeft') - this.alter.x) + 'px'
			});
		}

		if (scrollingDir == 'down' && win.t > this.oriPos.y && win.t + win.h > this.oriPos.y + this.box.offsetHeight) {
			// Scrolled down.
			if (win.t + win.h < this.posLimit.y + limit.offsetHeight) {
				// Stick box to either the window's top or bottom edge.
				y = this.box.offsetHeight < win.h ? 0 : win.h - this.box.offsetHeight;

				if ((pos == 'fixed' && top != y) || (pos == 'absolute' && top != (win.t + y - this.alter.y))) {
					dojo.setStyle(this.box, {
						position: fixed ? 'fixed' : 'absolute',
						top: (fixed ? y : (win.t + y - this.alter.y)) + 'px'
					});
				}
			} else if (win.t + this.box.offsetHeight > this.posLimit.y + limit.offsetHeight) {
				// Stick box to the limitation element's bottom edge.
				y = this.posLimit.y + (this.posLimit.y + limit.offsetHeight) - (this.oriPos.y + this.box.offsetHeight);
				if (this.posLimit.y != this.oriPos.y)
					y += (this.oriPos.y - this.posLimit.y);

				if (pos != 'absolute' || top != (y - this.alter.y)) {
					dojo.setStyle(this.box, {
						position: 'absolute',
						top: (y - this.alter.y) + 'px'
					});
				}
			} else if ((pos == 'fixed' && top != 0) || (pos == 'absolute' && top != (win.t - this.alter.y))) {
				// Stick box to the window's top edge.
				dojo.setStyle(this.box, {
					position: fixed ? 'fixed' : 'absolute',
					top: (fixed ? 0 : (win.t - this.alter.y)) + 'px'
				});
			}
		} else if (scrollingDir == 'up') {
			// Scrolled up.
			if (win.t < (top + this.alter.y) && win.t > this.oriPos.y) {
				// Stick box to the window's top edge.
				if ((pos == 'fixed' && top != 0) || (pos == 'absolute' && top != (win.t - this.alter.y))) {
					dojo.setStyle(this.box, {
						position: fixed ? 'fixed' : 'absolute',
						top: (fixed ? 0 : (win.t - this.alter.y)) + 'px'
					});
				}
			} else if (win.t < this.oriPos.y && (pos == 'fixed' || top != (this.oriPos.y - this.alter.y))) {
				// Restore original box position.
				dojo.setStyle(this.box, {
					position: 'absolute',
					top: (this.oriPos.y - this.alter.y) + 'px'
				});
			}
		}

		// Fix left position if box's parent is positioned relatively.
		if (fixed && this.alter.x > 0) {
			var p = dojo.getStyle(this.box, 'position');
			if (p == 'fixed')
				dojo.setStyle(this.box, 'left', this.oriPos.x + 'px');
			else if (p == 'absolute')
				dojo.setStyle(this.box, 'left', (this.oriPos.x - dojo.getStyle(this.box, 'marginLeft') - this.alter.x) + 'px');
		}

		// IE8 fail-over.
		if (window.ActiveXObject && document.querySelectorAll) {
			dojo.setStyle(this.box, 'visibility', 'hidden');
			dojo.setStyle(this.box, 'visibility', 'visible');
		}

		this.lastWinScrollTop = win.t;
		this.lastScrollingDir = scrollingDir;
	}
});
