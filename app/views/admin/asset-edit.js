window.asset = {

	el: '#asset',

	data: function () {
		return {
			data: window.$data,
			asset: window.$data.asset,
			sections: []
		}
	},

	created: function () {

		var sections = [];

		_.forIn (this.$options.components, function (component, name) {

			var options = component.options || {};

			if (options.section) {
				sections.push (_.extend ({name: name, priority: 0}, options.section));
			}

		});

		this.$set ('sections', _.sortBy (sections, 'priority'));

		this.resource = this.$resource ('api/assets/asset{/id}');
	},

	ready: function () {
		this.tab = UIkit.tab (this.$els.tab, {connect: this.$els.content});
	},

	watch: {
		'asset.data.type': function (newVal, oldVal) {
			if(newVal === 'inline'){
				this.asset.data.execution = 'immediately';
			}
		}
	},

	methods: {

		save: function () {
			var data = {asset: this.asset, id: this.asset.id};

			this.$broadcast ('save', data);

			this.resource.save ({id: this.asset.id}, data).then (function (res) {

				var data = res.data;

				if (!this.asset.id) {
					window.history.replaceState ({}, '', this.$url.route ('admin/assets/asset/edit', {id: data.asset.id}))
				}

				this.$set ('asset', data.asset);

				this.$notify ('Asset saved.');

			}, function (res) {
				this.$notify (res.data, 'danger');
			});
		}

	},

	components: {
		settings: require ('../../components/asset-edit.vue'),
		visibility: require ('../../components/asset-visibility.vue')
	}
};

Vue.ready (window.asset);