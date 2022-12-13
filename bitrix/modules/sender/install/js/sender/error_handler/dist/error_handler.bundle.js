this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"sender-user-error-handler-text\">\n\t\t\t", "\n\t\t\t<br>\n\t\t\t", "\n\t\t\t</div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ErrorHandler =
	/*#__PURE__*/
	function () {
	  function ErrorHandler() {
	    babelHelpers.classCallCheck(this, ErrorHandler);
	  }

	  babelHelpers.createClass(ErrorHandler, [{
	    key: "onError",
	    value: function onError(errorCode, data, callbackSuccess, callbackFailure) {
	      var handlers = this.getHandlers(callbackSuccess, callbackFailure);

	      if (handlers.hasOwnProperty(errorCode) && main_core.Type.isFunction(handlers[errorCode])) {
	        handlers[errorCode].apply(this, [data]);
	      }
	    }
	  }, {
	    key: "getHandlers",
	    value: function getHandlers(callbackSuccess, callbackFailure, extraData) {
	      return {
	        'WRONG_EMAIL_FROM': this.getWrongEmailFromHandler.bind(this, callbackSuccess, callbackFailure, extraData),
	        'FEATURE_NOT_AVAILABLE': this.getFeatureUnavailableHandler.bind(this, callbackSuccess, callbackFailure, extraData)
	      };
	    }
	  }, {
	    key: "getWrongEmailFromHandler",
	    value: function getWrongEmailFromHandler(callbackSuccess, callbackFailure, extraData, data) {
	      var _this = this;

	      if (extraData) {
	        Object.assign(data, extraData);
	      }

	      this.oncloseCalbackActive = true;

	      if (!this.wrongEmailFromPopup) {
	        this.wrongEmailFromPopup = BX.PopupWindowManager.create({
	          id: 'sender_user_error_wrongEmailFrom',
	          autoHide: true,
	          lightShadow: true,
	          closeByEsc: true,
	          overlay: {
	            backgroundColor: 'black',
	            opacity: 500
	          },
	          events: {
	            onPopupClose: function onPopupClose() {
	              if (_this.oncloseCalbackActive && main_core.Type.isFunction(callbackFailure)) {
	                callbackFailure(data);
	              }
	            }
	          }
	        });
	      }

	      this.wrongEmailFromPopup.setContent(main_core.Tag.render(_templateObject(), main_core.Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_TITLE'), main_core.Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_MESSAGE')));
	      this.wrongEmailFromPopup.setButtons([new BX.UI.Button({
	        text: main_core.Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_EDIT_EMAIL'),
	        color: BX.UI.Button.Color.SUCCESS,
	        onclick: function onclick() {
	          location.href = data.editUrl;
	          _this.oncloseCalbackActive = false;

	          _this.wrongEmailFromPopup.close();
	        }
	      }), new BX.UI.Button({
	        text: main_core.Loc.getMessage('SENDER_ERROR_HANDLER_WRONG_FROM_EMAIL_CANCEL'),
	        color: BX.UI.Button.Color.LINK,
	        onclick: function onclick() {
	          _this.wrongEmailFromPopup.close();
	        }
	      })]);
	      this.wrongEmailFromPopup.show();
	    }
	  }, {
	    key: "getFeatureUnavailableHandler",
	    value: function getFeatureUnavailableHandler(callbackSuccess, callbackFailure, extraData, data) {
	      if (extraData) {
	        Object.assign(data, extraData);
	      }

	      if (BX.Sender.B24License) {
	        BX.Sender.B24License.showPopup('Ad');
	      }

	      callbackFailure(data);
	    }
	  }]);
	  return ErrorHandler;
	}();

	exports.ErrorHandler = ErrorHandler;

}((this.BX.Sender = this.BX.Sender || {}),BX));
//# sourceMappingURL=error_handler.bundle.js.map
