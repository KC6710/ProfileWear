/**
 * Mega Filter Pack
 * 
 * @license Commercial
 * @author info@ocdemo.eu
 * 
 * All code within this file is copyright OC Mega Extensions.
 * You may not copy or reuse code within this file without written permission. 
 */

(function(){
	var Utils = (function(){
		var public = {},
			private = {};
			
		public.versionCompare = function(v1, v2, operator) {
			let i;
			let x;
			let compare = 0;
			const vm = {
			  dev: -6,
			  alpha: -5,
			  a: -5,
			  beta: -4,
			  b: -4,
			  RC: -3,
			  rc: -3,
			  '#': -2,
			  p: 1,
			  pl: 1
			};
			const _prepVersion = function (v) {
			  v = ('' + v).replace(/[_\-+]/g, '.');
			  v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.');
			  return (!v.length ? [-8] : v.split('.'));
			};
			const _numVersion = function (v) {
			  return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10));
			};
			v1 = _prepVersion(v1);
			v2 = _prepVersion(v2);
			x = Math.max(v1.length, v2.length);
			
			for (i = 0; i < x; i++) {
			  if (v1[i] === v2[i]) {
				continue;
			  }
			  v1[i] = _numVersion(v1[i]);
			  v2[i] = _numVersion(v2[i]);
			  if (v1[i] < v2[i]) {
				compare = -1;
				break
			  } else if (v1[i] > v2[i]) {
				compare = 1;
				break
			  }
			}
			
			if (!operator) {
			  return compare;
			}
			
			switch (operator) {
			  case '>':
			  case 'gt':
				return (compare > 0);
			  case '>=':
			  case 'ge':
				return (compare >= 0);
			  case '<=':
			  case 'le':
				return (compare <= 0);
			  case '===':
			  case '=':
			  case 'eq':
				return (compare === 0);
			  case '<>':
			  case '!==':
			  case 'ne':
				return (compare !== 0);
			  case '':
			  case '<':
			  case 'lt':
				return (compare < 0);
			  default:
				return null;
			}
		};
			
		public.inheritNamespaces = function( name, inherit_namespaces, namespace ) {
			var namespaces = typeof namespace != 'undefined' ? _.concat( namespace, inherit_namespaces ) : inherit_namespaces;

			if( typeof name != 'undefined' ) {
				return _.map( namespaces, function( v ){ return v + '.' + name; });
			}

			return namespaces;
		};
		
		public.inheritValue = function( obj, namespace, name, inherit_namespaces ){
			var keys = public.inheritNamespaces( namespace, name, inherit_namespaces ),
				value, i;

			for( i = 0; i < keys.length; i++ ) {
				var key = keys[i];
				
				value = _.get(obj, key);

				if( typeof value != 'undefined' && value != -1 ) {
					break;
				}
			}

			return value;
		};

		public.isPlainObject = function (obj) {
			return _.isObject(obj) && Object.getPrototypeOf(obj) == Object.prototype;
		};

		public.initializeAsObject = function( obj, keys ) {
			if( ! _.isArray( keys ) ) {
				keys = [ keys ];
			}
			
			function iao( key, prefix ) {
				var parts = key.split('.');
				
				prefix += prefix ? '.' : '';
				prefix += parts.shift();
				
				if( parts.length ) {
					var part = parts.shift();
					
					_.forEach( part.split('|'), function( k ){
						if( parts.length ) {
							k += '.' + parts.join('.');
						}
						
						iao( k, prefix );
					});
				} else {
					var value = _.get(obj, prefix);
					
					if( typeof value == 'undefined' || _.isArray( value ) ) {
						_.set(obj, prefix, _.extend({},value||{}));
					}
				}
			}
			
			_.forEach( keys, function( key ){
				iao( key, '' );
			});
		};

		public.url = (function(){
			var ie = document.documentMode,
				el = document.createElement('a');

			/**
			 * Encodes a Url parameter string.
			 *
			 * @param {Object} obj
			 */
			this.params = function (obj) {
				var params = [],
					escape = encodeURIComponent;

				params.add = function (key, value) {

					if (_.isFunction(value)) {
						value = value();
					}

					if (value === null) {
						value = '';
					}

					this.push(escape(key) + '=' + escape(value));
				};

				serialize(params, obj);

				return params.join('&').replace(/%20/g, '+');
			};

			/**
			 * Parse a URL and return its components.
			 *
			 * @param {String} url
			 */

			this.parse = function (url) {
				if (ie) {
					el.href = url;
					url = el.href;
				}

				el.href = url;

				return {
					href: el.href,
					protocol: el.protocol ? el.protocol.replace(/:$/, '') : '',
					port: el.port,
					host: el.host,
					hostname: el.hostname,
					pathname: el.pathname.charAt(0) === '/' ? el.pathname : '/' + el.pathname,
					search: el.search ? el.search.replace(/^\?/, '') : '',
					hash: el.hash ? el.hash.replace(/^#/, '') : ''
				};
			};

			function serialize(params, obj, scope) {
				var array = _.isArray(obj),
					plain = public.isPlainObject(obj),
					hash;

				_.each(obj, function (value, key) {

					hash = _.isObject(value) || _.isArray(value);

					if (scope) {
						key = scope + '[' + (plain || hash ? key : '') + ']';
					}

					if (!scope && array) {
						params.add(value.name, value.value);
					} else if (hash) {
						serialize(params, value, key);
					} else {
						params.add(key, value);
					}
				});
			}

			return this;
		})();

		return public;
	})();

	ocme.utils = function() {
		return Utils;
	};
})();