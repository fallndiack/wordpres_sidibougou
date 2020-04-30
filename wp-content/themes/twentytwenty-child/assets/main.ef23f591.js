// modules are defined as an array
// [ module function, map of requires ]
//
// map of requires is short require name -> numeric require
//
// anything defined in a previous bundle is accessed via the
// orig method which is the require for previous bundles
parcelRequire = (function (modules, cache, entry, globalName) {
  // Save the require from previous bundle to this closure if any
  var previousRequire = typeof parcelRequire === 'function' && parcelRequire;
  var nodeRequire = typeof require === 'function' && require;

  function newRequire(name, jumped) {
    if (!cache[name]) {
      if (!modules[name]) {
        // if we cannot find the module within our internal map or
        // cache jump to the current global require ie. the last bundle
        // that was added to the page.
        var currentRequire = typeof parcelRequire === 'function' && parcelRequire;
        if (!jumped && currentRequire) {
          return currentRequire(name, true);
        }

        // If there are other bundles on this page the require from the
        // previous one is saved to 'previousRequire'. Repeat this as
        // many times as there are bundles until the module is found or
        // we exhaust the require chain.
        if (previousRequire) {
          return previousRequire(name, true);
        }

        // Try the node require function if it exists.
        if (nodeRequire && typeof name === 'string') {
          return nodeRequire(name);
        }

        var err = new Error('Cannot find module \'' + name + '\'');
        err.code = 'MODULE_NOT_FOUND';
        throw err;
      }

      localRequire.resolve = resolve;
      localRequire.cache = {};

      var module = cache[name] = new newRequire.Module(name);

      modules[name][0].call(module.exports, localRequire, module, module.exports, this);
    }

    return cache[name].exports;

    function localRequire(x){
      return newRequire(localRequire.resolve(x));
    }

    function resolve(x){
      return modules[name][1][x] || x;
    }
  }

  function Module(moduleName) {
    this.id = moduleName;
    this.bundle = newRequire;
    this.exports = {};
  }

  newRequire.isParcelRequire = true;
  newRequire.Module = Module;
  newRequire.modules = modules;
  newRequire.cache = cache;
  newRequire.parent = previousRequire;
  newRequire.register = function (id, exports) {
    modules[id] = [function (require, module) {
      module.exports = exports;
    }, {}];
  };

  var error;
  for (var i = 0; i < entry.length; i++) {
    try {
      newRequire(entry[i]);
    } catch (e) {
      // Save first error but execute all entries
      if (!error) {
        error = e;
      }
    }
  }

  if (entry.length) {
    // Expose entry point to Node, AMD or browser globals
    // Based on https://github.com/ForbesLindesay/umd/blob/master/template.js
    var mainExports = newRequire(entry[entry.length - 1]);

    // CommonJS
    if (typeof exports === "object" && typeof module !== "undefined") {
      module.exports = mainExports;

    // RequireJS
    } else if (typeof define === "function" && define.amd) {
     define(function () {
       return mainExports;
     });

    // <script>
    } else if (globalName) {
      this[globalName] = mainExports;
    }
  }

  // Override the current require with this new one
  parcelRequire = newRequire;

  if (error) {
    // throw error from earlier, _after updating parcelRequire_
    throw error;
  }

  return newRequire;
})({"OeaJ":[function(require,module,exports) {
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

/**
 * Permet de rajouter la navigation tactile pour le carousel
 */
var CarouselTouchPlugin = /*#__PURE__*/function () {
  /**
   * @param {Carousel} carousel
   */
  function CarouselTouchPlugin(carousel) {
    _classCallCheck(this, CarouselTouchPlugin);

    carousel.container.addEventListener('dragstart', function (e) {
      return e.preventDefault();
    });
    carousel.container.addEventListener('mousedown', this.startDrag.bind(this));
    carousel.container.addEventListener('touchstart', this.startDrag.bind(this));
    window.addEventListener('mousemove', this.drag.bind(this));
    window.addEventListener('touchmove', this.drag.bind(this));
    window.addEventListener('touchend', this.endDrag.bind(this));
    window.addEventListener('mouseup', this.endDrag.bind(this));
    window.addEventListener('touchcancel', this.endDrag.bind(this));
    this.carousel = carousel;
  }
  /**
   * Démarre le déplacement au touché
   * @param {MouseEvent|TouchEvent} e
   */


  _createClass(CarouselTouchPlugin, [{
    key: "startDrag",
    value: function startDrag(e) {
      if (e.touches) {
        if (e.touches.length > 1) {
          return;
        } else {
          e = e.touches[0];
        }
      }

      this.origin = {
        x: e.screenX,
        y: e.screenY
      };
      this.width = this.carousel.containerWidth;
      this.carousel.disableTransition();
    }
    /**
     * Déplacement
     * @param {MouseEvent|TouchEvent} e
     */

  }, {
    key: "drag",
    value: function drag(e) {
      if (this.origin) {
        var point = e.touches ? e.touches[0] : e;
        var translate = {
          x: point.screenX - this.origin.x,
          y: point.screenY - this.origin.y
        };

        if (e.touches && Math.abs(translate.x) > Math.abs(translate.y)) {
          e.preventDefault();
          e.stopPropagation();
        }

        var baseTranslate = this.carousel.currentItem * -100 / this.carousel.items.length;
        this.lastTranslate = translate;
        this.carousel.translate(baseTranslate + 100 * translate.x / this.width);
      }
    }
    /**
     * Fin du déplacement
     * @param {MouseEvent|TouchEvent} e
     */

  }, {
    key: "endDrag",
    value: function endDrag(e) {
      if (this.origin && this.lastTranslate) {
        this.carousel.enableTransition();

        if (Math.abs(this.lastTranslate.x / this.carousel.carouselWidth) > 0.2) {
          if (this.lastTranslate.x < 0) {
            this.carousel.next();
          } else {
            this.carousel.prev();
          }
        } else {
          this.carousel.gotoItem(this.carousel.currentItem);
        }
      }

      this.origin = null;
    }
  }]);

  return CarouselTouchPlugin;
}();

var Carousel = /*#__PURE__*/function () {
  /**
   * This callback type is called `requestCallback` and is displayed as a global symbol.
   *
   * @callback moveCallback
   * @param {number} index
   */

  /**
   * @param {HTMLElement} element
   * @param {Object} options
   * @param {Object} [options.slidesToScroll=1] Nombre d'éléments à faire défiler
   * @param {Object} [options.slidesVisible=1] Nombre d'éléments visible dans un slide
   * @param {boolean} [options.loop=false] Doit-t-on boucler en fin de carousel
   * @param {boolean} [options.infinite=false]
   * @param {boolean} [options.pagination=false]
   * @param {boolean} [options.navigation=true]
   */
  function Carousel(element) {
    var _this = this;

    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    _classCallCheck(this, Carousel);

    this.element = element;
    this.options = Object.assign({}, {
      slidesToScroll: 1,
      slidesVisible: 1,
      loop: false,
      pagination: false,
      navigation: true,
      infinite: false
    }, options);

    if (this.options.loop && this.options.infinite) {
      throw new Error('Un carousel ne peut être à la fois en boucle et en infinie');
    }

    var children = [].slice.call(element.children);
    this.isMobile = false;
    this.currentItem = 0;
    this.moveCallbacks = [];
    this.offset = 0; // Modification du DOM

    this.root = this.createDivWithClass('carousel');
    this.container = this.createDivWithClass('carousel__container');
    this.root.setAttribute('tabindex', '0');
    this.root.appendChild(this.container);
    this.element.appendChild(this.root);
    this.items = children.map(function (child) {
      var item = _this.createDivWithClass('carousel__item');

      item.appendChild(child);
      return item;
    });

    if (this.options.infinite) {
      this.offset = this.options.slidesVisible + this.options.slidesToScroll;

      if (this.offset > children.length) {
        console.error("Vous n'avez pas assez d'élément dans le carousel", element);
      }

      this.items = [].concat(_toConsumableArray(this.items.slice(this.items.length - this.offset).map(function (item) {
        return item.cloneNode(true);
      })), _toConsumableArray(this.items), _toConsumableArray(this.items.slice(0, this.offset).map(function (item) {
        return item.cloneNode(true);
      })));
      this.gotoItem(this.offset, false);
    }

    this.items.forEach(function (item) {
      return _this.container.appendChild(item);
    });
    this.setStyle();

    if (this.options.navigation) {
      this.createNavigation();
    }

    if (this.options.pagination) {
      this.createPagination();
    } // Evenements


    this.moveCallbacks.forEach(function (cb) {
      return cb(_this.currentItem);
    });
    this.onWindowResize();
    window.addEventListener('resize', this.onWindowResize.bind(this));
    this.root.addEventListener('keyup', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'Right') {
        _this.next();
      } else if (e.key === 'ArrowLeft' || e.key === 'Left') {
        _this.prev();
      }
    });

    if (this.options.infinite) {
      this.container.addEventListener('transitionend', this.resetInfinite.bind(this));
    }

    new CarouselTouchPlugin(this);
  }
  /**
   * Applique les bonnes dimensions aux éléments du carousel
   */


  _createClass(Carousel, [{
    key: "setStyle",
    value: function setStyle() {
      var _this2 = this;

      var ratio = this.items.length / this.slidesVisible;
      this.container.style.width = ratio * 100 + "%";
      this.items.forEach(function (item) {
        return item.style.width = 100 / _this2.slidesVisible / ratio + "%";
      });
    }
    /**
     * Crée les flêches de navigation dans le DOM
     */

  }, {
    key: "createNavigation",
    value: function createNavigation() {
      var _this3 = this;

      var nextButton = this.createDivWithClass('carousel__next');
      var prevButton = this.createDivWithClass('carousel__prev');
      this.root.appendChild(nextButton);
      this.root.appendChild(prevButton);
      nextButton.addEventListener('click', this.next.bind(this));
      prevButton.addEventListener('click', this.prev.bind(this));

      if (this.options.loop === true) {
        return;
      }

      this.onMove(function (index) {
        if (index === 0) {
          prevButton.classList.add('carousel__prev--hidden');
        } else {
          prevButton.classList.remove('carousel__prev--hidden');
        }

        if (_this3.items[_this3.currentItem + _this3.slidesVisible] === undefined) {
          nextButton.classList.add('carousel__next--hidden');
        } else {
          nextButton.classList.remove('carousel__next--hidden');
        }
      });
    }
    /**
     * Crée la pagination dans le DOM
     */

  }, {
    key: "createPagination",
    value: function createPagination() {
      var _this4 = this;

      var pagination = this.createDivWithClass('carousel__pagination');
      var buttons = [];
      this.root.appendChild(pagination);

      var _loop = function _loop(i) {
        var button = _this4.createDivWithClass('carousel__pagination__button');

        button.addEventListener('click', function () {
          return _this4.gotoItem(i + _this4.offset);
        });
        pagination.appendChild(button);
        buttons.push(button);
      };

      for (var i = 0; i < this.items.length - 2 * this.offset; i = i + this.options.slidesToScroll) {
        _loop(i);
      }

      this.onMove(function (index) {
        var count = _this4.items.length - 2 * _this4.offset;
        var activeButton = buttons[Math.floor((index - _this4.offset) % count / _this4.options.slidesToScroll)];

        if (activeButton) {
          buttons.forEach(function (button) {
            return button.classList.remove('carousel__pagination__button--active');
          });
          activeButton.classList.add('carousel__pagination__button--active');
        }
      });
    }
  }, {
    key: "translate",
    value: function translate(percent) {
      this.container.style.transform = 'translate3d(' + percent + '%, 0, 0)';
    }
    /**
     *
     */

  }, {
    key: "next",
    value: function next() {
      this.gotoItem(this.currentItem + this.slidesToScroll);
    }
  }, {
    key: "prev",
    value: function prev() {
      this.gotoItem(this.currentItem - this.slidesToScroll);
    }
    /**
     * Déplace le carousel vers l'élément ciblé
     * @param {number} index
     * @param {boolean} [animation = true]
     */

  }, {
    key: "gotoItem",
    value: function gotoItem(index) {
      var animation = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

      if (index < 0) {
        if (this.options.loop) {
          index = this.items.length - this.slidesVisible;
        } else {
          return;
        }
      } else if (index >= this.items.length || this.items[this.currentItem + this.slidesVisible] === undefined && index > this.currentItem) {
        if (this.options.loop) {
          index = 0;
        } else {
          return;
        }
      }

      var translateX = index * -100 / this.items.length;

      if (animation === false) {
        this.disableTransition();
      }

      this.translate(translateX);
      this.container.offsetHeight; // force repaint

      if (animation === false) {
        this.enableTransition();
      }

      this.currentItem = index;
      this.moveCallbacks.forEach(function (cb) {
        return cb(index);
      });
    }
    /**
     * Déplace le container pour donner l'impression d'un slide infini
     */

  }, {
    key: "resetInfinite",
    value: function resetInfinite() {
      if (this.currentItem <= this.options.slidesToScroll) {
        this.gotoItem(this.currentItem + (this.items.length - 2 * this.offset), false);
      } else if (this.currentItem >= this.items.length - this.offset) {
        this.gotoItem(this.currentItem - (this.items.length - 2 * this.offset), false);
      }
    }
    /**
     * Rajoute un écouteur qui écoute le déplacement du carousel
     * @param {moveCallback} cb
     */

  }, {
    key: "onMove",
    value: function onMove(cb) {
      this.moveCallbacks.push(cb);
    }
    /**
     * Ecouteur pour le redimensionnement de la fenêtre
     */

  }, {
    key: "onWindowResize",
    value: function onWindowResize() {
      var _this5 = this;

      var mobile = window.innerWidth < 800;

      if (mobile !== this.isMobile) {
        this.isMobile = mobile;
        this.setStyle();
        this.moveCallbacks.forEach(function (cb) {
          return cb(_this5.currentItem);
        });
      }
    }
    /**
     * Helper pour créer une div avec une classe
     * @param {string} className
     * @returns {HTMLElement}
     */

  }, {
    key: "createDivWithClass",
    value: function createDivWithClass(className) {
      var div = document.createElement('div');
      div.setAttribute('class', className);
      return div;
    }
  }, {
    key: "disableTransition",
    value: function disableTransition() {
      this.container.style.transition = 'none';
    }
  }, {
    key: "enableTransition",
    value: function enableTransition() {
      this.container.style.transition = '';
    }
    /**
     * @returns {number}
     */

  }, {
    key: "slidesToScroll",
    get: function get() {
      return this.isMobile ? 1 : this.options.slidesToScroll;
    }
    /**
     * @returns {number}
     */

  }, {
    key: "slidesVisible",
    get: function get() {
      return this.isMobile ? 1 : this.options.slidesVisible;
    }
    /**
     * @returns {number}
     */

  }, {
    key: "containerWidth",
    get: function get() {
      return this.container.offsetWidth;
    }
    /**
     * @returns {number}
     */

  }, {
    key: "carouselWidth",
    get: function get() {
      return this.root.offsetWidth;
    }
  }]);

  return Carousel;
}();

exports.default = Carousel;
},{}],"d6sW":[function(require,module,exports) {
"use strict";

var _carousel = _interopRequireDefault(require("./modules/carousel.js"));

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

document.querySelector('.nav__burger').addEventListener('click', function () {
  document.querySelector('.nav__menu').classList.toggle('is-active');
  document.querySelector('.nav__burger').classList.toggle('is-active');
});
var slider = document.querySelector('.js-slider');

if (slider) {
  new _carousel.default(slider, {
    infinite: true
  });
}
},{"./modules/carousel.js":"OeaJ"}]},{},["d6sW"], null)
//# sourceMappingURL=main.ef23f591.js.map