window.wp = window.wp || {};

(function($){

	var rdv_admin = {

		start: function() {
			this.form();
			this.terms = new this.Collections.Terms();
			this.terms.fetch();
			this.terms.on( 'add', this.inject, this );
		},

		form: function() {
			var form;

	        this.form = new this.Views.Form();
	        this.form.inject( '.rendez-vous-form' );
		},

		inject: function() {
			this.view = new this.Views.Terms({ collection: this.terms });
			this.view.inject( '.rendez-vous-list-terms' );
		}
	};

	// Extend wp.Backbone.View with .prepare() and .inject()
	rdv_admin.View = wp.Backbone.View.extend({
		inject: function( selector ) {
			this.render();
			$(selector).html( this.el );
			this.views.ready();
		},

		prepare: function() {
			if ( ! _.isUndefined( this.model ) && _.isFunction( this.model.toJSON ) ) {
				return this.model.toJSON();
			} else {
				return {};
			}
		}
	});

	/* ------ */
	/* MODELS */
	/* ------ */

	rdv_admin.Models = {};

	rdv_admin.Models.Term = Backbone.Model.extend( {
		term: {},
	} );

	/* ----------- */
	/* COLLECTIONS */
	/* ----------- */
	rdv_admin.Collections = {};

	rdv_admin.Collections.Terms = Backbone.Collection.extend( {
		model: rdv_admin.Models.Term,

		sync: function( method, model, options ) {

			if( 'read' === method ) {
				options = options || {};
				options.context = this;
				options.data = _.extend( options.data || {}, {
					action: 'rendez_vous_get_terms'
				} );

				return wp.ajax.send( options );
			} else {
				console.log( method );
			}
	    },

		parse: function( resp, xhr ) {
			if ( ! _.isArray( resp ) ) {
				resp = [resp];
			}

			return resp;
		},

		insertTerm: function( name, options ) {
			model = this;

			return wp.ajax.post( 'rendez_vous_insert_term', {
				rendez_vous_type_name: name,
				/*nonce:               send the nonce here */
			} ).done( function( resp, status, xhr ) {
				model.add( model.parse( resp, xhr ), options );
				model.trigger( 'termAdded', model, options );
			} );
		},

		deleteTerm: function( term_id, options ) {
			model = this;

			return wp.ajax.post( 'rendez_vous_delete_term', {
				rendez_vous_type_id: term_id,
				/*nonce:               send the nonce here */
			} ).done( function( resp, status, xhr ) {
				model.remove( model.get( term_id ), options );
			} );
		}
	} );

	/* ----- */
	/* VIEWS */
	/* ----- */
	rdv_admin.Views = {};

	// Form to add new rendez-vous types
	rdv_admin.Views.Form = rdv_admin.View.extend({
		tagName:    'input',
		className:  'rdv-new-term regular-text',

		attributes: {
			type:        'text',
			placeholder: 'Name of your rendez-vous type'
		},

		events: {
			'keyup':  'addTerm',
		},

		addTerm: function( event ) {
			var type;

			event.preventDefault();

			if ( 13 != event.keyCode || ! $( event.target ).val() ) {
				return;
			}

			type = $( event.target ).val();
			$( event.target ).val( '' );
			$( event.target ).prop( 'disabled', true );

			this.listenTo( rdv_admin.terms, 'termAdded', this.termAdded );

			rdv_admin.terms.insertTerm( type );
		},

		termAdded: function( model ) {
			$( this.el ).prop( 'disabled', false );
			this.stopListening( rdv_admin.terms );
		}
	} );

	// List of terms
	rdv_admin.Views.Terms = rdv_admin.View.extend( {
		tagName:   'ul',
		className: 'rdv-terms',

		initialize: function() {
			_.each( this.collection.models, this.addItemView, this );
		},

		addItemView: function( terms ) {
			this.views.add( new rdv_admin.Views.Term( { model: terms } ) );
		}
	} );

	// Term item
	rdv_admin.Views.Term = rdv_admin.View.extend( {
		tagName:   'li',
		className: 'rdv-term',
		template: wp.template( 'rendez-vous-term' ),

		events: {
			'click .rdv-delete-item': 'deleteTerm',
		},

		initialize: function() {
			this.model.on( 'remove', this.remove, this );
		},

		deleteTerm: function( event ) {
			event.preventDefault();

			rdv_admin.terms.deleteTerm( $( event.target ).data( 'term_id' ) );
		}
	} );

	rdv_admin.start();

})(jQuery);
