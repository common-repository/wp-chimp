(function (element,blocks,lodash,data,apiFetch,editor,components,i18n) {
'use strict';

apiFetch = apiFetch && apiFetch.hasOwnProperty('default') ? apiFetch['default'] : apiFetch;

function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

function _extends() {
  _extends = Object.assign || function (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];

      for (var key in source) {
        if (Object.prototype.hasOwnProperty.call(source, key)) {
          target[key] = source[key];
        }
      }
    }

    return target;
  };

  return _extends.apply(this, arguments);
}

function ownKeys(object, enumerableOnly) {
  var keys = Object.keys(object);

  if (Object.getOwnPropertySymbols) {
    var symbols = Object.getOwnPropertySymbols(object);
    if (enumerableOnly) symbols = symbols.filter(function (sym) {
      return Object.getOwnPropertyDescriptor(object, sym).enumerable;
    });
    keys.push.apply(keys, symbols);
  }

  return keys;
}

function _objectSpread2(target) {
  for (var i = 1; i < arguments.length; i++) {
    var source = arguments[i] != null ? arguments[i] : {};

    if (i % 2) {
      ownKeys(source, true).forEach(function (key) {
        _defineProperty(target, key, source[key]);
      });
    } else if (Object.getOwnPropertyDescriptors) {
      Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
    } else {
      ownKeys(source).forEach(function (key) {
        Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
      });
    }
  }

  return target;
}

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) _setPrototypeOf(subClass, superClass);
}

function _getPrototypeOf(o) {
  _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

function _setPrototypeOf(o, p) {
  _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

function _possibleConstructorReturn(self, call) {
  if (call && (typeof call === "object" || typeof call === "function")) {
    return call;
  }

  return _assertThisInitialized(self);
}

/**
 * WordPress Dependencies.
 */
var DEFAULT_STATE = {
  subscriptionForms: [],
  notice: {
    status: null,
    message: null
  }
};
var actions = {
  fetchFromAPI: function fetchFromAPI(path) {
    return {
      type: 'FETCH_FROM_API',
      path: path
    };
  },
  postToAPI: function postToAPI(path, body) {
    return {
      type: 'POST_TO_API',
      path: path,
      body: body
    };
  },
  setAll: function setAll(subscriptionForms) {
    return {
      type: 'SET_ALL',
      subscriptionForms: subscriptionForms
    };
  },
  setItem: function setItem(item) {
    return {
      type: 'SET_ITEM',
      item: item
    };
  },
  setIsSaving: function setIsSaving(isSaving) {
    return {
      type: 'SET_STATUS',
      status: {
        isSaving: isSaving
      }
    };
  },
  unSetNotice: function unSetNotice() {
    return {
      type: 'SET_NOTICE',
      notice: {
        status: null,
        message: null
      }
    };
  },
  setNotice: function setNotice(status, message) {
    return {
      type: 'SET_NOTICE',
      notice: {
        status: status,
        message: message
      }
    };
  },
  saveItem:
  /*#__PURE__*/
  regeneratorRuntime.mark(function saveItem(item) {
    var body, keys, i, key, response;
    return regeneratorRuntime.wrap(function saveItem$(_context) {
      while (1) {
        switch (_context.prev = _context.next) {
          case 0:
            body = {};
            keys = Object.keys(item.attributes);
            i = 0;

          case 3:
            if (!(i < keys.length)) {
              _context.next = 12;
              break;
            }

            key = keys[i];

            if (lodash.has(item.attributes, key)) {
              _context.next = 7;
              break;
            }

            return _context.abrupt("continue", 9);

          case 7:
            if ('list_id' === key) {
              body[key] = item.attributes[key];
            }

            if (lodash.has(item.attributes[key], 'value')) {
              body[key] = item.attributes[key].value;
            }

          case 9:
            i++;
            _context.next = 3;
            break;

          case 12:
            _context.next = 14;
            return actions.postToAPI("/wp-chimp/v1/subscription-forms/".concat(body.list_id), body);

          case 14:
            response = _context.sent;
            return _context.abrupt("return", actions.setItem(response));

          case 16:
          case "end":
            return _context.stop();
        }
      }
    }, saveItem);
  })
};
var selectors = {
  getAll: function getAll(state) {
    return state.subscriptionForms || {};
  },
  getItem: function getItem(state, listId) {
    var item;

    for (var i = 0; i < state.subscriptionForms.length; i++) {
      if (state.subscriptionForms[i].list_id === listId) {
        item = state.subscriptionForms[i];
      }
    }

    return item || {};
  },
  getStatus: function getStatus(_ref) {
    var isSaving = _ref.isSaving;
    return {
      isSaving: isSaving
    };
  },
  getNotice: function getNotice(_ref2) {
    var notice = _ref2.notice;
    return {
      notice: notice
    };
  }
};
var controls = {
  FETCH_FROM_API: function FETCH_FROM_API(action) {
    return apiFetch({
      path: action.path
    });
  },
  POST_TO_API: function POST_TO_API(action) {
    return apiFetch({
      path: action.path,
      method: 'POST',
      body: JSON.stringify(action.body)
    });
  }
};
var resolvers = {
  getAll:
  /*#__PURE__*/
  regeneratorRuntime.mark(function getAll() {
    var all;
    return regeneratorRuntime.wrap(function getAll$(_context2) {
      while (1) {
        switch (_context2.prev = _context2.next) {
          case 0:
            _context2.next = 2;
            return actions.fetchFromAPI('/wp-chimp/v1/subscription-forms');

          case 2:
            all = _context2.sent;
            return _context2.abrupt("return", actions.setAll(all));

          case 4:
          case "end":
            return _context2.stop();
        }
      }
    }, getAll);
  }),
  getItem:
  /*#__PURE__*/
  regeneratorRuntime.mark(function getItem(listId) {
    var item;
    return regeneratorRuntime.wrap(function getItem$(_context3) {
      while (1) {
        switch (_context3.prev = _context3.next) {
          case 0:
            _context3.next = 2;
            return actions.fetchFromAPI("/wp-chimp/v1/subscription-forms/".concat(listId));

          case 2:
            item = _context3.sent;
            return _context3.abrupt("return", actions.setItem(item));

          case 4:
          case "end":
            return _context3.stop();
        }
      }
    }, getItem);
  })
};
var store = data.registerStore('wp-chimp/subscription-forms', {
  reducer: function reducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : DEFAULT_STATE;
    var action = arguments.length > 1 ? arguments[1] : undefined;

    switch (action.type) {
      case 'SET_ALL':
        return _objectSpread2({}, state, {
          subscriptionForms: action.subscriptionForms
        });

      case 'SET_ITEM':
        if (!lodash.includes(lodash.map(state.subscriptionForms, 'list_id'), action.item.list_id)) {
          state.subscriptionForms.push(action.item);
          return _objectSpread2({}, state, {
            subscriptionForms: state.subscriptionForms
          });
        } else {
          return _objectSpread2({}, state, {
            subscriptionForms: state.subscriptionForms.map(function (item) {
              if (item.list_id === action.item.list_id) {
                return _objectSpread2({}, action.item);
              }

              return item;
            })
          });
        }

      case 'SET_STATUS':
        return _objectSpread2({}, state, {}, action.status);

      case 'SET_NOTICE':
        return _objectSpread2({}, state, {
          notice: action.notice
        });
    }

    return state;
  },
  actions: actions,
  selectors: selectors,
  controls: controls,
  resolvers: resolvers
});

var getApiRootStatus = function getApiRootStatus() {
  var _wpChimpInlineState = wpChimpInlineState,
      restApiUrl = _wpChimpInlineState.restApiUrl;

  if (typeof wpChimpInlineState === 'undefined') {
    return false;
  }

  if (typeof restApiUrl === 'undefined' || !/\/wp-json\//.test(restApiUrl)) {
    return false;
  }

  return true;
};

/**
 * WordPress Dependencies.
 */
var _wpChimpInlineState = wpChimpInlineState;
var settingsUrl = _wpChimpInlineState.settingsUrl;

var FormNoticeUndefined = function FormNoticeUndefined(_ref) {
  var list_id = _ref.list_id;
  return element.createElement("div", {
    key: "form-undefined",
    className: "wp-chimp-notice wp-chimp-notice--error"
  }, element.createElement(element.RawHTML, {
    key: "form-undfined-content",
    className: "wp-chimp-notice__content"
  }, i18n.sprintf(i18n.__('One or some of the required data property, such as the %s, %s, and %s text, could not be retrieved from the selected MailChimp List. Please head to %s, and verify that the selected MailChimp List, %s, is actually present on this site.', 'wp-chimp'), "<strong>".concat(i18n.__('Heading', 'wp-chimp'), "</strong>"), "<strong>".concat(i18n.__('Email Placeholder', 'wp-chimp'), "</strong>"), "<strong>".concat(i18n.__('Button', 'wp-chimp'), "</strong>"), "<a href=\"".concat(settingsUrl, "\" target=\"_blank\" class=\"wp-chimp-notice__url\">").concat(i18n.__('the Settings page', 'wp-chimp'), "</a>"), "<code>".concat(list_id, "</code>"))));
};

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

var FormView =
/*#__PURE__*/
function (_Component) {
  _inherits(FormView, _Component);

  function FormView() {
    _classCallCheck(this, FormView);

    return _possibleConstructorReturn(this, _getPrototypeOf(FormView).apply(this, arguments));
  }

  _createClass(FormView, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          className = _this$props.className,
          attributes = _this$props.attributes,
          marketing_permissions = _this$props.marketing_permissions;

      var _ref = attributes || {},
          text_heading = _ref.text_heading,
          text_sub_heading = _ref.text_sub_heading,
          text_email_placeholder = _ref.text_email_placeholder,
          text_marketing_permissions = _ref.text_marketing_permissions,
          text_button = _ref.text_button,
          text_footer = _ref.text_footer;
      /**
       * First, check if the MailChimp List ID is present.
       *
       * This will also prevent Gutenberg from throwing an error
       * when one of the attributes is undefined because the
       * MailChimp List ID is not actually present.
       */


      if (lodash.isUndefined(attributes) || lodash.some(attributes, lodash.isUndefined)) {
        return element.createElement(FormNoticeUndefined, this.props.attributes);
      }

      return element.createElement("div", {
        className: "".concat(className)
      }, element.createElement("header", {
        className: "".concat(className, "__header")
      }, element.createElement("h3", {
        className: "".concat(className, "__heading")
      }, !lodash.isEmpty(text_heading.value) && text_heading.value), !lodash.isEmpty(text_sub_heading.value) && element.createElement("div", {
        className: "".concat(className, "__sub-heading")
      }, text_sub_heading.value)), element.createElement("div", {
        className: "".concat(className, "__form wp-chimp-form")
      }, element.createElement("div", {
        className: "wp-chimp-form-fields"
      }, element.createElement("input", {
        className: "wp-chimp-form-fields__item wp-chimp-form-fields-email",
        type: "email",
        placeholder: !lodash.isEmpty(text_email_placeholder.value) && text_email_placeholder.value,
        disabled: true
      }), true === marketing_permissions && !lodash.isEmpty(text_marketing_permissions.value) && element.createElement("div", {
        className: "wp-chimp-form-fields__item wp-chimp-form-fields-checkbox"
      }, element.createElement("input", {
        type: "checkbox",
        id: "".concat(attributes.list_id, "_marketing_permissions")
      }), element.createElement("label", {
        htmlFor: "".concat(attributes.list_id, "_marketing_permissions")
      }, text_marketing_permissions.value))), element.createElement("button", {
        className: "wp-chimp-form-button",
        type: "button"
      }, !lodash.isEmpty(text_button.value) && text_button.value)), !lodash.isEmpty(text_footer.value) && element.createElement("div", {
        className: "".concat(className, "__footer")
      }, text_footer.value));
    }
  }]);

  return FormView;
}(element.Component);

/**
 * WordPress Dependencies.
 */
var FormListSelect =
/*#__PURE__*/
function (_Component) {
  _inherits(FormListSelect, _Component);

  function FormListSelect() {
    _classCallCheck(this, FormListSelect);

    return _possibleConstructorReturn(this, _getPrototypeOf(FormListSelect).apply(this, arguments));
  }

  _createClass(FormListSelect, [{
    key: "render",
    value: function render() {
      var _this$props = this.props,
          className = _this$props.className,
          attributes = _this$props.attributes,
          setAttributes = _this$props.setAttributes,
          subscriptionForms = _this$props.subscriptionForms;
      return element.createElement("div", {
        className: "".concat(className, "__select-list")
      }, element.createElement("select", {
        value: attributes.list_id,
        onChange: function onChange(event) {
          return setAttributes({
            list_id: event.target.value ? event.target.value : attributes.list_id
          });
        }
      }, subscriptionForms.map(function (_ref, key) {
        var list_id = _ref.list_id,
            name = _ref.name;
        return element.createElement("option", {
          value: list_id,
          key: key
        }, name);
      })));
    }
  }]);

  return FormListSelect;
}(element.Component);

/**
 * WordPress Dependencies.
 */
var _wpChimpInlineState$1 = wpChimpInlineState;
var settingsUrl$1 = _wpChimpInlineState$1.settingsUrl;

var FormNoticeInactive = function FormNoticeInactive() {
  return element.createElement("div", {
    key: "form-inactive",
    className: "wp-chimp-notice wp-chimp-notice--warning"
  }, element.createElement(element.RawHTML, {
    key: "form-inactive-content",
    className: "wp-chimp-notice__content"
  }, i18n.sprintf(i18n.__('Subscription Form is currently inactive. You might haven\'t yet input the MailChimp API key to %1$s or your MailChimp account might not contain a %2$s.', 'wp-chimp'), "<a href=\"".concat(settingsUrl$1, "\" target=\"_blank\" class=\"wp-chimp-notice__url\">").concat(i18n.__('the Settings page', 'wp-chimp'), "</a>"), "<a href=\"https://kb.mailchimp.com/lists\" target=\"_blank\" class=\"wp-chimp-notice__url\">".concat(i18n.__('List', 'wp-chimp'), "</a>"))));
};

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

var FormEditor =
/*#__PURE__*/
function (_Component) {
  _inherits(FormEditor, _Component);

  function FormEditor() {
    _classCallCheck(this, FormEditor);

    return _possibleConstructorReturn(this, _getPrototypeOf(FormEditor).apply(this, arguments));
  }

  _createClass(FormEditor, [{
    key: "render",
    value: function render() {
      var _this = this;

      var _this$props = this.props,
          className = _this$props.className,
          subscriptionForms = _this$props.subscriptionForms;

      if (lodash.isUndefined(subscriptionForms) || lodash.isObject(subscriptionForms) && lodash.size(subscriptionForms) === 0) {
        return element.createElement(components.Placeholder, {
          className: "wp-chimp-placholder is-loading"
        }, element.createElement(components.Spinner, null));
      }

      if (lodash.size(subscriptionForms) < 1) {
        return element.createElement(FormNoticeInactive, null);
      }

      var subscriptionForm = subscriptionForms.find(function (obj) {
        return obj.list_id === _this.props.attributes.list_id;
      }) || {};
      return element.createElement(element.Fragment, null, element.createElement(editor.BlockControls, {
        key: "form-controls",
        className: "".concat(className, "__block-controls")
      }, element.createElement(components.Toolbar, {
        key: "form-toolbar"
      }, element.createElement(FormListSelect, this.props))), element.createElement(FormView, _extends({
        className: className
      }, subscriptionForm)));
    }
  }]);

  return FormEditor;
}(element.Component);

var FormEditor$1 = data.withSelect(function (select) {
  var subscriptionForms = select('wp-chimp/subscription-forms').getAll();
  return subscriptionForms ? {
    subscriptionForms: subscriptionForms
  } : {};
})(FormEditor);

/**
 * WordPress Depenencies
 */
/**
 * Internal Dependencies.
 */

var _wpChimpInlineState$2 = wpChimpInlineState;
var mailchimpApiStatus = _wpChimpInlineState$2.mailchimpApiStatus;
/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://wordpress.org/gutenberg/handbook/block-api/
 */

blocks.registerBlockType('wp-chimp/subscription-form', {
  /**
   * The icon shown in the Gutenberg block list.
   *
   * @see https://developer.wordpress.org/resource/dashicons/
   *
   * @type {string|Object}
   */
  icon: element.createElement("svg", {
    width: "24",
    height: "24",
    viewBox: "0 0 24 24"
  }, element.createElement("g", null, element.createElement("path", {
    fill: "none",
    d: "M0 0h24v24H0V0z"
  }), element.createElement("path", {
    d: "M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 4.99L4 6h16zm0 12H4V8l8 5 8-5v10z"
  }))),

  /**
   * The edit function describes the structure of your block in the context
   * of the editor.
   *
   * This represents what the editor will render when the block is used.
   *
   * @see https://wordpress.org/gutenberg/handbook/block-edit-save/#edit
   *
   * @param {Object} [props] Properties passed from the editor.
   * @return {Element}       Element to render.
   */
  edit: function edit(props) {
    if (getApiRootStatus() !== true || mailchimpApiStatus !== true) {
      return element.createElement(FormNoticeInactive, null);
    }

    return element.createElement(FormEditor$1, _extends({}, props, {
      className: "wp-chimp-subscription-form"
    }));
  },

  /**
   * The save function defines the way in which the different attributes
   * should be combined into the final markup, which is then serialized
   * by Gutenberg into `post_content`.
   *
   * @see https://wordpress.org/gutenberg/handbook/block-edit-save/#save
   *
   * @param {Object} [props] Properties passed from the editor.
   * @return {string} The [wp-chimp] shortcode.
   */
  save: function save(props) {
    return props.attributes.list_id ? "[wp-chimp list_id=\"".concat(props.attributes.list_id, "\"]") : '[wp-chimp]';
  }
});

}(wp.element,wp.blocks,lodash,wp.data,wp.apiFetch,wp.editor,wp.components,wp.i18n));
