/**
* View
*/
var EnviraScheduleView = Backbone.View.extend({

	/**
    * The Tag Name and Tag's Class(es)
    */
	tagName: 'div',
	className: 'envira-schedule',

	/**
	* Template
	* - The template to load inside the above tagName element
	*/
	template: wp.template('envira-meta-editor-schedule'),

	/**
	* Initialize
	*/
	initialize: function (args) {
		this.model = args.model;
	},

	/**
	* Render
	*/
	render: function () {

		// Set the template HTML
		this.$el.html(this.template(this.model.attributes));

		this.$el.trigger('render');

		jQuery(document).trigger('enviraScheduleRender');

		return this;

	}

});

// Add the view to the SoliloquyChildViews, so that it's loaded in the modal
EnviraGalleryChildViews.push(EnviraScheduleView);