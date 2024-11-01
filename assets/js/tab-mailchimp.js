(function (element,lodash,data,apiFetch,url,i18n,components,ClipboardJS,compose,ContentLoader) {
'use strict';

apiFetch = apiFetch && apiFetch.hasOwnProperty('default') ? apiFetch['default'] : apiFetch;
ClipboardJS = ClipboardJS && ClipboardJS.hasOwnProperty('default') ? ClipboardJS['default'] : ClipboardJS;
ContentLoader = ContentLoader && ContentLoader.hasOwnProperty('default') ? ContentLoader['default'] : ContentLoader;

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) {
  try {
    var info = gen[key](arg);
    var value = info.value;
  } catch (error) {
    reject(error);
    return;
  }

  if (info.done) {
    resolve(value);
  } else {
    Promise.resolve(value).then(_next, _throw);
  }
}

function _asyncToGenerator(fn) {
  return function () {
    var self = this,
        args = arguments;
    return new Promise(function (resolve, reject) {
      var gen = fn.apply(self, args);

      function _next(value) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value);
      }

      function _throw(err) {
        asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err);
      }

      _next(undefined);
    });
  };
}

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
var isOdd = function isOdd(number) {
  var n = number % 2;
  return n === 1;
};

/**
 * WordPress Dependencies.
 */
/**
 * Cache the lists to sessionStorage
 *
 * @since 0.5.0
 *
 * @param {Number} page
 * @param {Object} data
 */

function setListsCache(page, data$$1) {
  var sessionKey = "wp_chimp_lists_page_".concat(page);

  if (null === window.sessionStorage.getItem(sessionKey)) {
    window.sessionStorage.setItem(sessionKey, JSON.stringify(data$$1));
  }
}
/**
 * Get the lists cache from sessionStorage
 *
 * @since 0.5.0
 *
 * @param {integer} page The current page.
 */

function getListsCache(page) {
  var sessionKey = "wp_chimp_lists_page_".concat(page);
  var listsCache = window.sessionStorage.getItem(sessionKey);

  if (null !== listsCache) {
    listsCache = JSON.parse(listsCache);
  }

  return listsCache && lodash.has(listsCache, 'lists') ? listsCache : null;
}
/**
 * Clear the `wp_chimp_lists_page_` sessionStorage.
 *
 * @since 0.5.0
 */

var clearListsCache = function clearListsCache() {
  var arr = [];

  for (var i = 0; i < window.sessionStorage.length; i++) {
    if (-1 < window.sessionStorage.key(i).indexOf('wp_chimp_lists_page_')) {
      arr.push(window.sessionStorage.key(i));
    }
  }

  if (0 < arr.length) {
    // Iterate over arr and remove the items by key
    for (var _i = 0; _i < arr.length; _i++) {
      window.sessionStorage.removeItem(arr[_i]);
    }
  }
};
/**
 * Cache the API status in sessionStorage
 *
 * @since 0.5.0
 *
 * @param {Object} data
 * @param {Integer} expirationMin
 */

var setMailChimpStatusCache = function setMailChimpStatusCache(data$$1, expirationMin) {
  var expirationMS = expirationMin * 60 * 1000;
  var record = {
    value: JSON.stringify(data$$1),
    timestamp: new Date().getTime() + expirationMS
  };
  window.sessionStorage.setItem('wp_chimp_mailchimp_api_status', JSON.stringify(record));
  return data$$1;
};
/**
 * Retrieve the API status from the sessionStorage
 *
 * @since 0.5.0
 */

var getMailChimpStatusCache = function getMailChimpStatusCache() {
  var record = JSON.parse(window.sessionStorage.getItem('wp_chimp_mailchimp_api_status'));

  if (!record) {
    return false;
  }

  if (new Date().getTime() > record.timestamp) {
    window.sessionStorage.removeItem('wp_chimp_mailchimp_api_status');
    return false;
  } else {
    return JSON.parse(record.value);
  }
};

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

var _createContext = element.createContext();
var Provider = _createContext.Provider;
var Consumer = _createContext.Consumer;

var _wpChimpInlineState = wpChimpInlineState;
var wpRestNonce = _wpChimpInlineState.wpRestNonce;
var mailchimpApiStatus = _wpChimpInlineState.mailchimpApiStatus;
var listsInit = _wpChimpInlineState.listsInit;
var listsPerPage = _wpChimpInlineState.listsPerPage;

var TableListsProvider =
/*#__PURE__*/
function (_Component) {
  _inherits(TableListsProvider, _Component);

  /**
   * Component initial State.
   *
   * @since 0.6.0
   * @var {Object}
   */
  function TableListsProvider(props) {
    var _this;

    _classCallCheck(this, TableListsProvider);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(TableListsProvider).call(this, props));

    _defineProperty(_assertThisInitialized(_this), "state", {
      fetching: false,
      syncing: false,
      lists: [],
      listsPage: 1,
      // Initial and default page to retrieve from the API.
      listsTotal: 0,
      // The total number of lists retrieved.
      listsTotalPages: 0,
      // The total pages on the tabel.
      pageNext: 0,
      pagePrev: 0,
      detailId: '',
      modalId: ''
    });

    _defineProperty(_assertThisInitialized(_this), "fetchLists", function () {
      var args = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      var page = lodash.has(args, 'query') && args.query.page ? args.query.page : 1;
      var cache = getListsCache(page);

      if (!_this.state.syncing && cache) {
        var pagePrev = parseInt(cache.listsPage > 1 ? cache.listsPage - 1 : 0, 10);
        var pageNext = parseInt(cache.listsPage < cache.listsTotalPages ? cache.listsPage + 1 : 0, 10);

        _this.setState(_objectSpread2({}, cache, {
          pageNext: pageNext,
          pagePrev: pagePrev
        }));

        return;
      }

      args = _objectSpread2({
        beforeSend: function beforeSend() {
          return _this.setState({
            fetching: true
          });
        },
        path: '/wp-chimp/v1/lists',
        headers: {
          'X-WP-Nonce': wpRestNonce
        },
        query: {
          'page': _this.state.listsPage,
          'per_page': listsPerPage
        },
        parse: false
      }, args);

      if (args.path) {
        args.path = url.addQueryArgs(args.path, {
          page: args.query.page,
          per_page: args.query.per_page
        });
      }

      if (lodash.has(args, 'beforeSend')) {
        args.beforeSend();
      }

      apiFetch(_objectSpread2({}, args)).then(_this.handleSuccess).then(_this.handleResponse).catch(function () {
        return _this.setState({
          fetching: false,
          syncing: false
        });
      });
    });

    _defineProperty(_assertThisInitialized(_this), "syncLists", function () {
      if (_this.state.syncing) {
        return;
      }

      clearListsCache();

      _this.fetchLists({
        path: '/wp-chimp/v1/sync/lists',
        beforeSend: function beforeSend() {
          return _this.setState({
            syncing: true
          });
        }
      });
    });

    _defineProperty(_assertThisInitialized(_this), "handleSuccess", function (response) {
      _this.setState({
        fetching: false,
        syncing: false
      });

      if (response.status === 204) {
        return null;
      }

      if (response.status >= 200 && response.status < 300) {
        return response.json ? response : Promise.reject(response);
      }
    });

    _defineProperty(_assertThisInitialized(_this), "handleResponse", function (response) {
      var listsPage = parseInt(response.headers.get('X-WP-Chimp-Lists-Page'), 10);
      var listsTotal = parseInt(response.headers.get('X-WP-Chimp-Lists-Total'), 10);
      var listsTotalPages = parseInt(response.headers.get('X-WP-Chimp-Lists-TotalPages'), 10);
      var pagePrev = parseInt(listsPage > 1 ? listsPage - 1 : 0, 10);
      var pageNext = parseInt(listsPage < listsTotalPages ? listsPage + 1 : 0, 10);
      response.json().then(function (lists) {
        setListsCache(listsPage, {
          lists: lists,
          listsPage: listsPage,
          listsTotal: listsTotal,
          listsTotalPages: listsTotalPages
        });

        _this.setState({
          lists: lists,
          listsPage: listsPage,
          listsTotal: listsTotal,
          listsTotalPages: listsTotalPages,
          pageNext: pageNext,
          pagePrev: pagePrev
        });
      });
    });

    return _this;
  }
  /**
   * Invoked immediately after a component is mounted (inserted into the tree).
   *
   * @since 0.6.0
   *
   * @returns {Void}
   */


  _createClass(TableListsProvider, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      if (!mailchimpApiStatus || !getApiRootStatus()) {
        return;
      }

      if (false === listsInit) {
        this.syncLists();
      } else {
        this.fetchLists();
      }
      /**
       * Attache the syncButton element to the syncLists method,
       * so when we click the
       *
       * TODO: This is less ideal.
       * Refactor this to include the "syncButton" into a React Component.
       */


      this.props.syncButtonDOM.addEventListener('click', this.syncLists); // Clear the Lists cache when the window is reloaded.

      window.addEventListener('beforeunload', clearListsCache);
    }
    /**
     * Fetch data to display on the Table List.
     *
     * @since 0.6.0
     *
     * @param {Object} args
     * @returns {Void}
     */

  }, {
    key: "render",
    value: function render$$1() {
      var _this2 = this;

      return element.createElement(Provider, {
        value: _objectSpread2({}, this.state, {
          toggleListDetail: function toggleListDetail(listId) {
            return _this2.setState({
              detailId: _this2.state.detailId === listId ? '' : listId
            });
          },
          toggleModal: function toggleModal(listId) {
            return _this2.setState({
              modalId: _this2.state.modalId === listId ? '' : listId
            });
          },
          navigateToPage: function navigateToPage(targetPage) {
            if (!Number.isInteger(targetPage) || targetPage <= 0) {
              return;
            }

            _this2.fetchLists({
              query: {
                page: targetPage,
                per_page: listsPerPage
              }
            });
          }
        })
      }, this.props.children);
    }
  }]);

  return TableListsProvider;
}(element.Component);

function IconBool(props) {
  var status = props.status;
  var icon = status ? 'yes' : 'no-alt';
  return element.createElement("span", _extends({}, props, {
    className: "dashicons dashicons-".concat(icon, " wp-chimp-icon-bool wp-chimp-icon-bool--").concat(icon)
  }));
}

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

var _wpChimpInlineState$1 = wpChimpInlineState;
var mailchimpApiDc = _wpChimpInlineState$1.mailchimpApiDc;
/**
 * Render the MailChimp list item on the table.
 *
 * @since 0.6.0
 */

function TableListsItem(props) {
  var nth = props.nth,
      item = props.item,
      empty = props.empty,
      onToggleDetail = props.onToggleDetail,
      onToggleModal = props.onToggleModal,
      isDetail = props.isDetail;
  var stats = (item || {}).stats || {};
  var member_count = stats.member_count;

  if (empty) {
    return element.createElement("tr", {
      className: "wp-chimp-table__tr wp-chimp-table__tr--odd"
    }, element.createElement("td", {
      colSpan: "5",
      height: "16"
    }, i18n.__('No MailChimp Lists found.', 'wp-chimp')));
  }

  var statKeys = Object.keys(stats || {});
  var statKeysLength = statKeys.length;
  var gdprBadge = item.marketing_permissions ? element.createElement("span", {
    className: "row-gdpr"
  }, "GDPR") : null;
  var listTitle = element.createElement("span", {
    className: "row-title"
  }, item.name);
  var buttonDetail;

  if (!lodash.isUndefined(member_count) && statKeysLength > 1) {
    listTitle = element.createElement("span", {
      className: "row-title".concat(statKeysLength > 1 ? ' has-stats' : ''),
      onClick: onToggleDetail
    }, item.name);
    buttonDetail = element.createElement(components.Tooltip, {
      text: i18n.__('View Detail', 'wp-chimp')
    }, element.createElement(components.Button, {
      isDefault: true,
      isSmall: true,
      onClick: onToggleDetail
    }, element.createElement(components.Dashicon, {
      icon: isDetail ? 'arrow-up-alt2' : 'arrow-down-alt2',
      size: "14"
    })));
  }

  return element.createElement(element.Fragment, null, element.createElement("tr", {
    className: "wp-chimp-table__tr".concat(isOdd(nth) ? ' wp-chimp-table__tr--odd' : '')
  }, element.createElement("td", {
    className: "wp-chimp-table__td td-list-id"
  }, element.createElement("code", {
    className: "wp-chimp-code-inline"
  }, item.list_id)), element.createElement("td", {
    className: "wp-chimp-table__td td-name"
  }, element.createElement("h3", null, listTitle, gdprBadge)), element.createElement("td", {
    className: "wp-chimp-table__td td-suscribers"
  }, member_count), element.createElement("td", {
    className: "wp-chimp-table__td td-double-optin"
  }, element.createElement(IconBool, {
    status: item.double_optin
  })), element.createElement("td", {
    className: "wp-chimp-table__td td-actions"
  }, element.createElement(components.ButtonGroup, {
    className: "wp-chimp-button-group".concat(statKeysLength > 1 ? ' has-third-button' : '')
  }, element.createElement(components.Tooltip, {
    text: i18n.__('Subscription Form Tool', 'wp-chimp')
  }, element.createElement(components.Button, {
    isDefault: true,
    isSmall: true,
    onClick: onToggleModal
  }, element.createElement(components.Dashicon, {
    icon: "feedback",
    size: "14"
  }))), element.createElement(components.Tooltip, {
    text: i18n.__('View in MailChimp', 'wp-chimp')
  }, element.createElement(components.Button, {
    isDefault: true,
    isSmall: true,
    href: "https://".concat(mailchimpApiDc, ".admin.mailchimp.com/lists/members/?id=").concat(item.web_id),
    target: "_blank",
    rel: "external noopener noreferrer"
  }, element.createElement(components.Dashicon, {
    icon: "external",
    size: "14"
  }))), buttonDetail))));
}

function InputReadOnly(props) {
  return element.createElement("input", _extends({}, props, {
    className: "wp-chimp-input wp-chimp-input--readonly",
    readOnly: true
  }));
}

/**
 * WordPress Dependencies.
 */
/**
 * External Dependencies.
 */

/**
 * Render the table row showing the List detail.
 *
 * @since 0.6.0
 *
 * @param {Object} props The component properties.
 */

var CopyButton =
/*#__PURE__*/
function (_Component) {
  _inherits(CopyButton, _Component);

  function CopyButton(props) {
    var _this;

    _classCallCheck(this, CopyButton);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(CopyButton).call(this, props));

    _defineProperty(_assertThisInitialized(_this), "state", {
      copied: false
    });

    _this.copiedTimeout;
    return _this;
  }

  _createClass(CopyButton, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this2 = this;

      this.clipboard = new ClipboardJS(this.button);
      this.clipboard.on('success', function (event) {
        clearTimeout(_this2.copiedTimeout);

        _this2.setState({
          copied: true
        });

        _this2.copiedTimeout = setTimeout(function () {
          return _this2.setState({
            copied: false
          });
        }, 3000);
        event.clearSelection();
      });
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      this.clipboard.destroy();
    }
  }, {
    key: "render",
    value: function render$$1() {
      var _this3 = this;

      var clipboardText = this.props.clipboardText;
      return element.createElement(components.Tooltip, {
        text: this.state.copied ? i18n.__('Copied', 'wp-chimp') : i18n.__('Copy the shortcode', 'wp-chimp')
      }, element.createElement(components.Button, {
        ref: function ref(_ref) {
          _this3.button = _ref;
        },
        isDefault: true,
        "data-clipboard-text": clipboardText
      }, element.createElement(components.Dashicon, {
        icon: this.state.copied ? 'yes' : 'clipboard',
        size: "14"
      })));
    }
  }]);

  return CopyButton;
}(element.Component);

/**
 * WordPress Dependencies.
 */
/**
 * Render the List detail tabs.
 *
 * @since 0.6.0
 */

var TableListsDetailTabs =
/*#__PURE__*/
function (_Component) {
  _inherits(TableListsDetailTabs, _Component);

  /**
   * Component initial State.
   *
   * @since 0.6.0
   * @var {Object}
   */
  function TableListsDetailTabs(props) {
    var _this;

    _classCallCheck(this, TableListsDetailTabs);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(TableListsDetailTabs).call(this, props));

    _defineProperty(_assertThisInitialized(_this), "state", {
      activeTabIndex: 0
    });

    _defineProperty(_assertThisInitialized(_this), "handleTabClick", function (activeTabIndex) {
      _this.setState({
        activeTabIndex: activeTabIndex
      });
    });

    return _this;
  }
  /**
   * Handle the tab click and set the Active tab.
   *
   * @since 0.6.0
   *
   * @returns {Void}
   */


  _createClass(TableListsDetailTabs, [{
    key: "render",
    value: function render$$1() {
      var _this2 = this;

      var tabsComponent = [];

      if (this.props.children && !Array.isArray(this.props.children)) {
        tabsComponent.push(this.props.children);
      } else {
        tabsComponent = this.props.children;
      }

      if (tabsComponent.length < 1) {
        return null;
      }

      var tabsMenu = [];
      tabsMenu = tabsComponent.map(function (tab, index) {
        if (!tab || !tab.props.name) {
          return null;
        }

        return element.createElement("li", {
          key: index,
          className: "wp-chimp-list-detail-tabs__menu-item".concat(index === _this2.state.activeTabIndex ? ' active' : ''),
          onClick: function onClick() {
            return _this2.handleTabClick(index);
          }
        }, tab.props.name);
      });
      return element.createElement(element.Fragment, null, element.createElement("div", {
        className: "wp-chimp-list-detail-tabs"
      }, tabsMenu.length > 0 && element.createElement(element.Fragment, null, element.createElement("ul", {
        className: "wp-chimp-list-detail-tabs__menu"
      }, tabsMenu), element.createElement("div", {
        className: "wp-chimp-list-detail-tabs__content"
      }, tabsComponent[this.state.activeTabIndex]))));
    }
  }]);

  return TableListsDetailTabs;
}(element.Component);

/**
 * WordPress Dependencies.
 */
/**
 * Render the List Stats table.
 *
 * @since 0.6.0
 *
 * @param {Object} props The component properties.
 */

function TableListsItemStats(_ref) {
  var data$$1 = _ref.data;
  var avgOpenRate = Number.parseFloat(data$$1.open_rate).toFixed(2);
  var avgClickRate = Number.parseFloat(data$$1.click_rate).toFixed(2);
  var avgSubRate = Number.parseFloat(data$$1.avg_sub_rate).toFixed(2);
  var avgUnsubRate = Number.parseFloat(data$$1.avg_unsub_rate).toFixed(2);
  return element.createElement(element.Fragment, null, element.createElement("div", {
    className: "stats-section stats-section-overview"
  }, element.createElement("div", {
    className: "stats-section-overview__item"
  }, element.createElement("div", {
    className: "stats-count"
  }, data$$1.member_count), element.createElement("div", {
    className: "stats-label"
  }, i18n.__('Subscribed Contacts', 'wp-chimp'))), element.createElement("div", {
    className: "stats-section-overview__item"
  }, element.createElement("div", {
    className: "stats-count"
  }, data$$1.unsubscribe_count), element.createElement("div", {
    className: "stats-label"
  }, i18n.__('Unsubscribed Contacts', 'wp-chimp'))), element.createElement("div", {
    className: "stats-section-overview__item"
  }, element.createElement("div", {
    className: "stats-count"
  }, data$$1.cleaned_count), element.createElement("div", {
    className: "stats-label"
  }, i18n.__('Cleaned Contacts', 'wp-chimp')))), element.createElement("div", {
    className: "stats-section stats-section-list-performance"
  }, element.createElement("div", {
    className: "stats-section-list-performance__item"
  }, element.createElement("div", {
    className: "stats-section-list-performance__item-data"
  }, element.createElement("h5", {
    className: "stats-title"
  }, i18n.__('Average Open Rate', 'wp-chimp')), element.createElement("div", {
    className: "stats-count"
  }, "".concat(avgOpenRate, "%"))), element.createElement("div", {
    className: "stats-meter"
  }, element.createElement("div", {
    className: "stats-meter__bar",
    style: {
      width: "".concat(Math.round(avgOpenRate), "%")
    }
  }))), element.createElement("div", {
    className: "stats-section-list-performance__item"
  }, element.createElement("div", {
    className: "stats-section-list-performance__item-data"
  }, element.createElement("h5", {
    className: "stats-title"
  }, i18n.__('Average Click Rate', 'wp-chimp')), element.createElement("div", {
    className: "stats-count"
  }, "".concat(avgClickRate, "%"))), element.createElement("div", {
    className: "stats-meter"
  }, element.createElement("div", {
    className: "stats-meter__bar",
    style: {
      width: "".concat(Math.round(avgClickRate), "%")
    }
  }))), element.createElement("div", {
    className: "stats-section-list-performance__item"
  }, element.createElement("div", {
    className: "stats-section-list-performance__item-data"
  }, element.createElement("h5", {
    className: "stats-title"
  }, i18n.__('Average Subscribe Rate', 'wp-chimp')), element.createElement("div", {
    className: "stats-count"
  }, "".concat(avgSubRate, "%"))), element.createElement("div", {
    className: "stats-meter"
  }, element.createElement("div", {
    className: "stats-meter__bar",
    style: {
      width: "".concat(Math.round(avgSubRate), "%")
    }
  }))), element.createElement("div", {
    className: "stats-section-list-performance__item"
  }, element.createElement("div", {
    className: "stats-section-list-performance__item-data"
  }, element.createElement("h5", {
    className: "stats-title"
  }, i18n.__('Average Unsubscribe Rate', 'wp-chimp')), element.createElement("div", {
    className: "stats-count"
  }, "".concat(avgUnsubRate, "%"))), element.createElement("div", {
    className: "stats-meter"
  }, element.createElement("div", {
    className: "stats-meter__bar",
    style: {
      width: "".concat(Math.round(avgUnsubRate), "%")
    }
  })))));
}

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

/**
 * Render the table row showing the List detail.
 *
 * @since 0.6.0
 *
 * @param {Object} props The component properties.
 */

function TableListsItemDetail(props) {
  var nth = props.nth,
      item = props.item,
      isDetail = props.isDetail,
      onToggleDetail = props.onToggleDetail;

  var _ref = item || {},
      list_id = _ref.list_id,
      stats = _ref.stats;

  var tableClassName = 'wp-chimp-table';
  var detailFooterClassName = 'wp-chimp-list-detail-footer';
  var detailShortcodeClassName = 'wp-chimp-list-detail-shortcode';

  if (!isDetail) {
    return null;
  }

  return element.createElement("tr", {
    className: "".concat(tableClassName, "__tr").concat(isOdd(nth) ? " ".concat(tableClassName, "__tr--odd") : '', " wp-chimp-list-detail")
  }, element.createElement("td", {
    className: "".concat(tableClassName, "__td td-detail"),
    colSpan: "5"
  }, element.createElement(TableListsDetailTabs, null, element.createElement(TableListsItemStats, {
    name: i18n.__('Stats', 'wp-chimp'),
    data: stats || {}
  })), element.createElement("div", {
    className: "".concat(detailFooterClassName)
  }, element.createElement("div", {
    className: "".concat(detailFooterClassName, "__item wp-chimp-list-detail-close")
  }, element.createElement(components.Button, {
    isDefault: true,
    onClick: onToggleDetail
  }, i18n.__('Close', 'wp-chimp'))), element.createElement("div", {
    className: "".concat(detailFooterClassName, "__item ").concat(detailShortcodeClassName)
  }, element.createElement("label", {
    className: "".concat(detailShortcodeClassName, "__label"),
    htmlFor: "wp-chimp-list-shortcode-".concat(list_id)
  }, i18n.__('Shortcode', 'wp-chimp')), element.createElement(InputReadOnly, {
    className: "".concat(detailShortcodeClassName, "__content"),
    id: "wp-chimp-list-shortcode-".concat(list_id),
    value: "[wp-chimp list_id=\"".concat(list_id, "\"]")
  }), element.createElement(CopyButton, {
    className: "".concat(detailShortcodeClassName, "__button"),
    clipboardText: "[wp-chimp list_id=\"".concat(list_id, "\"]")
  })))));
}

/**
 * WordPress Dependencies.
 */
/**
 * Render the controls to edit the Subscription Form.
 *
 * @since 0.7.0
 *
 * @param {Object} props
 */

function ModalControls(_ref) {
  var attributes = _ref.attributes,
      _onChange = _ref.onChange,
      _onFocus = _ref.onFocus;
  var attributeKeys = Object.keys(lodash.isObject(attributes) && !lodash.isEmpty(attributes) ? attributes : []);
  return attributeKeys.map(function (name) {
    var className = 'wp-chimp-subscription-form-control';
    var element$$1 = null;

    switch (attributes[name].editor_type) {
      case 'text':
        element$$1 = element.createElement(components.TextControl, {
          className: "".concat(className, " ").concat(className, "-text"),
          label: attributes[name].label,
          value: attributes[name].value ? attributes[name].value : '',
          help: attributes[name].description ? attributes[name].description : '',
          onChange: function onChange(value) {
            return _onChange(value, name);
          },
          onFocus: function onFocus() {
            return _onFocus(name);
          }
        });
        break;

      case 'textarea':
      case 'richtext':
        // Temporary.
        element$$1 = element.createElement(components.TextareaControl, {
          className: "".concat(className, " ").concat(className, "-textarea"),
          name: name,
          label: attributes[name].label,
          value: attributes[name].value ? attributes[name].value : '',
          help: attributes[name].description ? attributes[name].description : '',
          onChange: function onChange(value) {
            return _onChange(value, name);
          },
          onFocus: function onFocus() {
            return _onFocus(name);
          }
        });
        break;
    }

    return element$$1;
  });
}

/**
 * WordPress dependencies
 */
var ModalNotice = compose.compose(data.withSelect(function (select) {
  var _select = select('wp-chimp/subscription-forms'),
      getNotice = _select.getNotice;

  return _objectSpread2({}, getNotice() || {});
}), data.withDispatch(function (dispatch) {
  var _dispatch = dispatch('wp-chimp/subscription-forms'),
      unSetNotice = _dispatch.unSetNotice;

  return {
    unSetNotice: unSetNotice
  };
}))(function (_ref) {
  var notice = _ref.notice,
      className = _ref.className,
      unSetNotice = _ref.unSetNotice;
  var status = notice.status,
      message = notice.message;
  return status && message && element.createElement(components.Notice, {
    status: status,
    onRemove: unSetNotice,
    className: "".concat(className, "-notice")
  }, message);
});

/**
 * WordPress Dependencies.
 */
var _wpChimpInlineState$2 = wpChimpInlineState;
var settingsUrl = _wpChimpInlineState$2.settingsUrl;

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
    value: function render$$1() {
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
/**
 * External Dependencies.
 */

/**
 * Render the placeholder for Subscription Form Preview.
 *
 * @since 0.7.0
 *
 * @returns {Element}
 */

function ModalPreviewLoader() {
  return element.createElement(ContentLoader, {
    ariaLabel: i18n.__('Loading Subscription Form Preview', 'wp-chimp'),
    height: 250,
    width: 640,
    speed: 2,
    preserveAspectRatio: "xMaxYMin slice",
    primaryColor: "#edeff0",
    secondaryColor: "#d7dade",
    style: {
      width: '100%',
      maxWidth: '640px',
      height: '100%',
      maxHeight: '250px'
    }
  }, element.createElement("rect", {
    x: "25",
    y: "35",
    width: "180",
    height: "18"
  }), element.createElement("rect", {
    x: "25",
    y: "63",
    width: "560",
    height: "18"
  }));
}
/**
 * Render the placeholder for inputs & controls on the Modal sidebar.
 *
 * @since 0.7.0
 *
 * @returns {Element}
 */

function ModalControlsLoader() {
  return element.createElement(ContentLoader, {
    ariaLabel: i18n.__('Loading Preview Controls', 'wp-chimp'),
    height: 250,
    width: 300,
    speed: 2,
    preserveAspectRatio: "xMaxYMin slice",
    primaryColor: "#edeff0",
    secondaryColor: "#d7dade",
    style: {
      width: '100%',
      maxWidth: '300px',
      height: '100%',
      maxHeight: '250px'
    }
  }, element.createElement("rect", {
    x: "0",
    y: "0",
    width: "180",
    height: "18"
  }), element.createElement("rect", {
    x: "0",
    y: "28",
    width: "300",
    height: "28"
  }));
}

/**
 * WordPress dependencies.
 */
/**
 * Internal dependencies.
 */

function ModalFormPreview(_ref) {
  var preview = _ref.preview,
      className = _ref.className;
  return element.createElement("div", {
    className: className
  }, lodash.isObject(preview) && !lodash.isEmpty(preview) ? element.createElement(FormView, _extends({
    className: "wp-chimp-subscription-form"
  }, preview)) : element.createElement(ModalPreviewLoader, null));
}

/**
 * WordPress Dependencies.
 */
var ModalSaveButton = compose.compose(data.withSelect(function (select) {
  var _select = select('wp-chimp/subscription-forms'),
      getStatus = _select.getStatus;

  return _objectSpread2({}, getStatus());
}), data.withDispatch(function (dispatch, _ref) {
  var _onSave = _ref.onSave;

  var _dispatch = dispatch('wp-chimp/subscription-forms'),
      saveItem = _dispatch.saveItem,
      setIsSaving = _dispatch.setIsSaving,
      setNotice = _dispatch.setNotice;

  return {
    onSave: function () {
      var _onSave2 = _asyncToGenerator(
      /*#__PURE__*/
      regeneratorRuntime.mark(function _callee() {
        var response;
        return regeneratorRuntime.wrap(function _callee$(_context) {
          while (1) {
            switch (_context.prev = _context.next) {
              case 0:
                setIsSaving(true);
                _context.next = 3;
                return saveItem(_onSave());

              case 3:
                response = _context.sent;
                setIsSaving(false);

                if (response) {
                  setNotice('success', i18n.__('Settings saved.', 'wp-chimp'));
                } else {
                  setNotice('error', i18n.__('An unexpected error has occured.', 'wp-chimp'));
                }

              case 6:
              case "end":
                return _context.stop();
            }
          }
        }, _callee);
      }));

      function onSave() {
        return _onSave2.apply(this, arguments);
      }

      return onSave;
    }()
  };
}))(function (_ref2) {
  var isSaving = _ref2.isSaving,
      onSave = _ref2.onSave;
  return element.createElement(components.Button, {
    isPrimary: true,
    isBusy: isSaving,
    onClick: onSave
  }, isSaving ? i18n.__('Saving Changes...', 'wp-chimp') : i18n.__('Save Changes', 'wp-chimp'));
});

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

var modalClassName = 'wp-chimp-modal';
var formToolClassName = 'wp-chimp-subscription-form-tool';
/**
 * Render the modal dialog.
 *
 * @since 0.7.0
 */

var TableListModal =
/*#__PURE__*/
function (_Component) {
  _inherits(TableListModal, _Component);

  function TableListModal(props) {
    var _this;

    _classCallCheck(this, TableListModal);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(TableListModal).call(this, props));

    _defineProperty(_assertThisInitialized(_this), "state", {
      focus: null
    });

    _defineProperty(_assertThisInitialized(_this), "handleValueChange", function (value, name) {
      _this.setState(_defineProperty({}, name, value));
    });

    _defineProperty(_assertThisInitialized(_this), "handleFocusState", function (name) {
      _this.setState({
        focus: name
      });
    });

    return _this;
  }
  /**
   * Save the new value input in the state.
   *
   * @since 0.7.0
   *
   * @param {String} value The value to save to the state.
   * @param {String} name The name of the input element handling the value.
   */


  _createClass(TableListModal, [{
    key: "withStateAttributes",

    /**
     * Merge the attribute value with the value saved in the state.
     *
     * @since 0.7.0
     *
     * @param {Object} subscriptionForm
     */
    value: function withStateAttributes(subscriptionForm) {
      if (lodash.isUndefined(subscriptionForm.attributes)) {
        return;
      }

      var compiled = lodash.cloneDeep(subscriptionForm);

      for (var key in this.state) {
        if (lodash.has(compiled.attributes, key)) {
          var attrValue = lodash.has(this.state, key) ? this.state[key] : '';
          compiled.attributes[key].value = attrValue;
        }
      }

      return compiled;
    }
  }, {
    key: "render",
    value: function render$$1() {
      /**
       * Return the subcription form with the merged attributes value
       * from the state.
       */
      var _this$props = this.props,
          list = _this$props.list,
          onClose = _this$props.onClose;
      var name = list.name,
          marketing_permissions = list.marketing_permissions;
      var subscriptionForm = this.withStateAttributes(this.props.subscriptionForm);
      /**
       * Ideally the list of attributes should not be hardcoded.
       * TODO: Make the list of attributes configurable.
       */

      var topAttributes = ['text_heading', 'text_sub_heading'];
      var bottomAttributes = ['text_button', 'text_footer'];
      var mergeAttributes = ['text_email_placeholder'];

      if (true === marketing_permissions) {
        mergeAttributes.push('text_marketing_permissions');
      }

      return element.createElement(components.Modal, {
        title: name,
        onRequestClose: onClose,
        contentLabel: i18n.__('Subscription Form Tool', 'wp-chimp'),
        className: modalClassName,
        overlayClassName: "".concat(modalClassName, "-overlay"),
        shouldCloseOnClickOutside: true
      }, element.createElement("div", {
        className: "".concat(modalClassName, "__content ").concat(formToolClassName)
      }, element.createElement("div", {
        className: "".concat(modalClassName, "-main")
      }, element.createElement("h2", {
        className: "screen-reader-text"
      }, i18n.__('Subscription Form Preview', 'wp-chimp')), element.createElement(ModalNotice, {
        className: modalClassName
      }), element.createElement(ModalFormPreview, {
        className: "".concat(formToolClassName, "__preview"),
        preview: subscriptionForm
      })), element.createElement("div", {
        className: "".concat(modalClassName, "-sidebar")
      }, element.createElement("h2", {
        className: "screen-reader-text"
      }, i18n.__('Subscription Form Editor', 'wp-chimp')), element.createElement("div", {
        className: "".concat(formToolClassName, "__editor")
      }, lodash.isObject(subscriptionForm) && lodash.isObject(subscriptionForm.attributes) && lodash.size(subscriptionForm) > 0 && lodash.size(subscriptionForm.attributes) > 0 ? element.createElement(element.Fragment, null, element.createElement(ModalControls, {
        attributes: lodash.pick(subscriptionForm.attributes, topAttributes),
        onChange: this.handleValueChange,
        onFocus: this.handleFocusState
      }), element.createElement(ModalControls, {
        attributes: lodash.pick(subscriptionForm.attributes, mergeAttributes),
        onChange: this.handleValueChange,
        onFocus: this.handleFocusState
      }), element.createElement(ModalControls, {
        attributes: lodash.pick(subscriptionForm.attributes, bottomAttributes),
        onChange: this.handleValueChange,
        onFocus: this.handleFocusState
      }), element.createElement(components.Panel, {
        className: "".concat(formToolClassName, "__panel")
      }, element.createElement(components.PanelBody, {
        title: i18n.__('Notices', 'wp-chimp'),
        initialOpen: false,
        className: "".concat(formToolClassName, "__panel-body")
      }, element.createElement(components.PanelRow, {
        className: "".concat(formToolClassName, "__panel-row")
      }, element.createElement(ModalControls, {
        attributes: lodash.pickBy(subscriptionForm.attributes, function (value, key) {
          return lodash.startsWith(key, 'text_notice_');
        })
      }))))) : element.createElement(ModalControlsLoader, null)))), element.createElement("div", {
        className: "".concat(modalClassName, "__footer")
      }, element.createElement(components.Button, {
        isDefault: true,
        onClick: onClose
      }, i18n.__('Cancel', 'wp-chimp')), element.createElement(ModalSaveButton, {
        onSave: function onSave() {
          return subscriptionForm;
        }
      })));
    }
  }]);

  return TableListModal;
}(element.Component);

var TableListsModal = compose.compose(data.withSelect(function (select, _ref) {
  var list = _ref.list;

  var _select = select('wp-chimp/subscription-forms'),
      getItem = _select.getItem,
      getStatus = _select.getStatus;

  return _objectSpread2({
    subscriptionForm: getItem(list.list_id)
  }, getStatus());
}), data.withDispatch(function (dispatch, _ref2) {
  var onToggleModal = _ref2.onToggleModal;

  var _dispatch = dispatch('wp-chimp/subscription-forms'),
      unSetNotice = _dispatch.unSetNotice;

  return {
    onClose: function onClose() {
      onToggleModal();
      unSetNotice();
    }
  };
}))(TableListModal);

/**
 * WordPress Dependencies.
 */
/**
 * Render the input component on the pagination.
 *
 * @since 0.6.0
 */

var TableListsPaginationInput =
/*#__PURE__*/
function (_Component) {
  _inherits(TableListsPaginationInput, _Component);

  function TableListsPaginationInput() {
    var _getPrototypeOf2;

    var _this;

    _classCallCheck(this, TableListsPaginationInput);

    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    _this = _possibleConstructorReturn(this, (_getPrototypeOf2 = _getPrototypeOf(TableListsPaginationInput)).call.apply(_getPrototypeOf2, [this].concat(args)));

    _defineProperty(_assertThisInitialized(_this), "state", {
      value: 1
      /**
       * Invoked immediately after updating occurs.
       *
       * @since 0.6.0
       *
       * @param {Object} prevProps
       */

    });

    _defineProperty(_assertThisInitialized(_this), "componentDidUpdate", function (prevProps) {
      if (_this.props.page !== prevProps.page) {
        _this.setState({
          value: _this.props.page
        });
      }
    });

    _defineProperty(_assertThisInitialized(_this), "handleInputChange", function (event) {
      _this.setState({
        value: _this.handleInputValue(event.target.value)
      });
    });

    _defineProperty(_assertThisInitialized(_this), "handleInputKeyPress", function (event) {
      var keyCode = event.which || event.keyCode;

      if (keyCode === 13) {
        // "Enter" key.
        event.preventDefault();
      }

      return _this.handleInputValue(event.target.value);
    });

    _defineProperty(_assertThisInitialized(_this), "handleInputValue", function (value) {
      var maxPage = _this.props.maxPage;
      var parsedValue = parseInt(value, 10);

      if (parsedValue < 1) {
        return 1;
      }

      if (parsedValue > maxPage) {
        return maxPage;
      }

      return parsedValue >= 1 && parsedValue <= maxPage ? parsedValue : '';
    });

    return _this;
  }

  _createClass(TableListsPaginationInput, [{
    key: "render",
    value: function render$$1() {
      var _this2 = this;

      var navigateHandler = this.props.navigateHandler;
      return element.createElement("input", {
        className: "current-page",
        type: "text",
        size: "3",
        "aria-describedby": "table-paging",
        value: this.state.value,
        onChange: this.handleInputChange,
        onKeyPress: function onKeyPress(event) {
          return navigateHandler(_this2.handleInputKeyPress(event));
        }
      });
    }
  }]);

  return TableListsPaginationInput;
}(element.Component);

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

/**
 * Render the pagination button element in the TableListsPagination Component.
 *
 * @since 0.6.0
 *
 * @param {Object} props The component properties.
 */

function PaginationButton(props) {
  var navigate = props.navigate,
      page = props.page,
      children = props.children;
  return 0 === page ? element.createElement("span", {
    className: "tablenav-pages-navspan button disabled",
    "aria-hidden": "true"
  }, children) : element.createElement("button", _extends({
    type: "button"
  }, props, {
    className: "button ".concat(navigate, "-page").concat(0 === page ? ' inactive' : '')
  }), children);
}
/**
 * Render the pagination for the List table.
 *
 * @since 0.6.0
 */


function TableListsPagination() {
  return element.createElement(Consumer, null, function (_ref) {
    var navigateToPage = _ref.navigateToPage,
        listsPage = _ref.listsPage,
        listsTotal = _ref.listsTotal,
        listsTotalPages = _ref.listsTotalPages,
        pageNext = _ref.pageNext,
        pagePrev = _ref.pagePrev;

    if (listsTotalPages < 2) {
      return null;
    }

    return element.createElement("div", {
      className: "tablenav"
    }, element.createElement("div", {
      className: "tablenav-pages",
      id: "wp-chimp-table-pagination"
    }, element.createElement("span", {
      className: "displaying-num"
    }, "".concat(listsTotal, " ").concat(i18n._nx('item', 'items', listsTotal, 'Table pagination: denoting the lists number, e.g. 10 items', 'wp-chimp'))), element.createElement(PaginationButton, {
      navigate: "prev",
      page: pagePrev,
      onClick: function onClick() {
        return navigateToPage(pagePrev);
      }
    }, "\u2039"), element.createElement("span", {
      className: "paging-input"
    }, element.createElement("label", {
      className: "screen-reader-text",
      htmlFor: "current-page-selector"
    }, i18n.__('Current Page', 'wp-chimp')), element.createElement(TableListsPaginationInput, {
      page: listsPage,
      maxPage: listsTotalPages,
      navigateHandler: function navigateHandler(pageInput) {
        return navigateToPage(pageInput);
      }
    }), element.createElement("span", {
      className: "tablenav-paging-text"
    }, i18n.__('of', 'wp-chimp'), element.createElement("span", {
      className: "total-pages"
    }, listsTotalPages))), element.createElement(PaginationButton, {
      navigate: "next",
      page: pageNext,
      onClick: function onClick() {
        return navigateToPage(pageNext);
      }
    }, "\u203A")));
  });
}

/**
 * WordPress Dependencies.
 */
/**
 * External Dependencies.
 */

/**
 * Render the table data element when syncing the Lists data from MailChimp.
 *
 * @since 0.6.0
 *
 * @param {Object} props The component properties.
 */

function ItemLoaderSyncing(_ref) {
  var colSpan = _ref.colSpan,
      height = _ref.height,
      children = _ref.children;
  return element.createElement("td", {
    colSpan: colSpan,
    height: height
  }, element.createElement("div", {
    className: "wp-chimp-spinner spinner"
  }), " ", children);
}
/**
 * Render the table data element when syncing the Lists data from MailChimp.
 *
 * @since 0.6.0
 *
 * @param {Object} props The component properties.
 */

function ItemLoaderFetching(_ref2) {
  var colSpan = _ref2.colSpan;
  var placeholder = [];

  for (var i = 0; colSpan > i; i++) {
    placeholder.push(element.createElement("td", {
      className: "wp-chimp-table-td-placeholder td-placeholder",
      key: i
    }, element.createElement(ContentLoader, {
      ariaLabel: i18n.__('Loading table data', 'wp-chimp'),
      height: 12,
      width: 100,
      speed: 2,
      preserveAspectRatio: "xMaxYMin slice",
      primaryColor: "#edeff0",
      secondaryColor: "#d7dade",
      style: {
        width: '100%',
        height: "".concat(12, "px")
      }
    }, element.createElement("rect", {
      x: "0",
      y: "0",
      width: 100,
      height: 12
    }))));
  }

  return placeholder;
}

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

/**
 * Render the table to display the MailChimp lists.
 *
 * @since 0.6.0
 */

function TableLists() {
  var tableClassName = 'wp-chimp-table';
  return element.createElement(Consumer, null, function (_ref) {
    var toggleListDetail = _ref.toggleListDetail,
        toggleModal = _ref.toggleModal,
        detailId = _ref.detailId,
        modalId = _ref.modalId,
        fetching = _ref.fetching,
        syncing = _ref.syncing,
        lists = _ref.lists;
    var tableRows;

    if (lists.length > 0) {
      tableRows = lists.map(function (list, index) {
        var props = {
          nth: index + 1,
          item: list
        };
        return element.createElement(element.Fragment, {
          key: list.list_id
        }, element.createElement(TableListsItem, _extends({}, props, {
          onToggleDetail: function onToggleDetail() {
            return toggleListDetail(list.list_id);
          },
          onToggleModal: function onToggleModal() {
            return toggleModal(list.list_id);
          },
          isDetail: detailId === list.list_id
        })), detailId ? element.createElement(TableListsItemDetail, _extends({}, props, {
          onToggleDetail: function onToggleDetail() {
            return toggleListDetail(list.list_id);
          },
          isDetail: detailId === list.list_id
        })) : null);
      });
    } else {
      tableRows = element.createElement(TableListsItem, {
        empty: true
      });
    }

    var tableLoader;

    if (syncing) {
      tableLoader = element.createElement("tr", {
        className: "".concat(tableClassName, "__tr ").concat(tableClassName, "__tr--odd")
      }, element.createElement(ItemLoaderSyncing, {
        colSpan: "5",
        height: "16"
      }, i18n.__('Please wait while we are syncing the Lists data from MailChimp...', 'wp-chimp')));
    }

    if (fetching) {
      tableLoader = element.createElement("tr", {
        className: "".concat(tableClassName, "__tr ").concat(tableClassName, "__tr--odd")
      }, element.createElement(ItemLoaderFetching, {
        colSpan: "5",
        height: "16"
      }));
    }

    return element.createElement(element.Fragment, null, element.createElement("table", {
      className: "widefat ".concat(tableClassName),
      id: "".concat(tableClassName, "-lists")
    }, element.createElement("thead", null, element.createElement("tr", null, element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-list-id")
    }, i18n.__('ID', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-name")
    }, i18n.__('Name', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-subscribers")
    }, i18n.__('Subscribers', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-double-optin")
    }, i18n.__('Double Opt-in', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-actions")
    }, i18n.__('Actions', 'wp-chimp')))), element.createElement("tbody", null, syncing || fetching ? tableLoader : tableRows), element.createElement("tfoot", null, element.createElement("tr", null, element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-list-id")
    }, i18n.__('ID', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-name")
    }, i18n.__('Name', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-subscribers")
    }, i18n.__('Subscribers', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-double-optin")
    }, i18n.__('Double Opt-in', 'wp-chimp')), element.createElement("th", {
      scope: "col",
      className: "".concat(tableClassName, "__th th-actions")
    }, i18n.__('Actions', 'wp-chimp'))))), element.createElement(TableListsPagination, null), !lodash.isEmpty(modalId) && element.createElement(TableListsModal, {
      onToggleModal: function onToggleModal() {
        return toggleModal(modalId);
      },
      list: lists.find(function (list) {
        return list.list_id === modalId;
      })
    }));
  });
}

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

var _wpChimpInlineState$3 = wpChimpInlineState;
var wpRestNonce$1 = _wpChimpInlineState$3.wpRestNonce;
var mailchimpApiStatus$1 = _wpChimpInlineState$3.mailchimpApiStatus;
var restApiUrl = _wpChimpInlineState$3.restApiUrl;
var listsInit$1 = _wpChimpInlineState$3.listsInit;

var _createContext$1 = element.createContext();
var Provider$1 = _createContext$1.Provider;
var Consumer$1 = _createContext$1.Consumer;

var ApiStatusProvider =
/*#__PURE__*/
function (_Component) {
  _inherits(ApiStatusProvider, _Component);

  /**
   * Component initial State.
   *
   * @since 0.6.0
   * @var {Object}
   */
  function ApiStatusProvider(props) {
    var _this;

    _classCallCheck(this, ApiStatusProvider);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(ApiStatusProvider).call(this, props));

    _defineProperty(_assertThisInitialized(_this), "state", {
      pinging: false,
      pingStatus: {}
    });

    _defineProperty(_assertThisInitialized(_this), "onPingAlways", function (_ref) {
      var connected = _ref.connected,
          title = _ref.title;

      if (connected) {
        /**
         * If the site is able to connect to MailChimp API,
         * enable the button.
         *
         * TODO: This is less ideal.
         * So refactor this to include the "syncButton" into a React Component.
         */
        _this.props.syncButtonDOM.removeAttribute('disabled');
      }

      var pingStatus = {
        'pinged': true,
        // Tell the browser that we've pinged the MailChimp API.
        'connected': connected,
        'title': title
      };
      setMailChimpStatusCache(pingStatus, 0.5);

      _this.setState({
        pinging: false,
        pingStatus: pingStatus
      });
    });

    return _this;
  }
  /**
   * Invoked immediately after a component is mounted (inserted into the tree).
   *
   * @since 0.6.0
   *
   * @returns {Void}
   */


  _createClass(ApiStatusProvider, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var _this2 = this;

      if (!mailchimpApiStatus$1 || !getApiRootStatus()) {
        return;
      }

      var statusCache = getMailChimpStatusCache();
      var connected = statusCache.connected;

      if (connected && listsInit$1) {
        /**
         * If the site is able to connect to MailChimp API,
         * enable the button.
         *
         * TODO: This is less ideal.
         * So refactor this to include the "syncButton" into a React Component.
         */
        this.props.syncButtonDOM.removeAttribute('disabled');
      }

      if (statusCache && listsInit$1) {
        this.setState({
          pingStatus: statusCache
        });
      } else {
        jQuery.ajax({
          type: 'GET',
          headers: {
            'X-WP-Nonce': wpRestNonce$1
          },
          url: "".concat(restApiUrl, "/ping"),
          beforeSend: function beforeSend() {
            return _this2.setState({
              pinging: true
            });
          }
        }).always(this.onPingAlways);
      }
    }
    /**
     * Handle response when the `/ping` request is completed.
     *
     * @since 0.6.0
     *
     * @param {Object} response
     */

  }, {
    key: "render",
    value: function render$$1() {
      return element.createElement(Provider$1, {
        value: _objectSpread2({}, this.state)
      }, this.props.children);
    }
  }]);

  return ApiStatusProvider;
}(element.Component);

/**
 * WordPress Dependencies.
 */
/**
 * Internal Dependencies.
 */

function ApiStatus() {
  return element.createElement(Consumer$1, null, function (_ref) {
    var pingStatus = _ref.pingStatus,
        pinging = _ref.pinging;
    var className = 'wp-chimp-mailchimp-api-status-light';

    var statusTitle = pingStatus.title || i18n.__('Oops! an unexpected error occured.', 'wp-chimp');

    return element.createElement(element.Fragment, null, element.createElement("span", {
      className: "".concat(className, " ").concat(className).concat(pingStatus.connected ? '--connected' : '--disconnected')
    }), element.createElement("span", null, pinging ? i18n.__('Connecting...') : statusTitle));
  });
}

/**
 * WordPress Depenencies
 */
/**
 * Internal Dependencies.
 */

var listsRoot = document.querySelector('#wp-chimp-lists');
var apiStatusRoot = document.querySelector('#wp-chimp-mailchimp-api-status');
var syncButton = document.querySelector('#wp-chimp-sync-lists-button');

if (lodash.isElement(listsRoot)) {
  element.render(element.createElement(TableListsProvider, {
    syncButtonDOM: syncButton
  }, element.createElement(TableLists, null)), listsRoot);
}

if (lodash.isElement(apiStatusRoot)) {
  element.render(element.createElement(ApiStatusProvider, {
    syncButtonDOM: syncButton
  }, element.createElement(ApiStatus, null)), apiStatusRoot);
}

}(wp.element,lodash,wp.data,wp.apiFetch,wp.url,wp.i18n,wp.components,ClipboardJS,wp.compose,ContentLoader));
