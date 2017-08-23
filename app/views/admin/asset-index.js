window.assets = {

	el: '#assets',

	data: function () {
		return _.merge({
			assets: false,
			config: {
				filter: this.$session.get('assets.filter', {order: 'date desc', limit: 25})
			},
			pages: 0,
			count: '',
			selected: []
		}, window.$data);
	},
	ready: function () {
		this.resource = this.$resource('api/assets/asset{/id}');

		this.$watch('config.page', this.load, {immediate: true});
	},
	watch: {
		'config.filter': {
			handler: function (filter) {
				if (this.config.page) {
					this.config.page = 0;
				} else {
					this.load();
				}

				this.$session.set('assets.filter', filter);
			},
			deep: true
		}
	},
	computed: {
		statusOptions: function () {
			var options = _.map(this.$data.statuses, function (status, id) {
				return {text: status, value: id};
			});

			return [{label: this.$trans('Filter by'), options: options}];
		}
	},
	methods: {
		active: function (asset) {
			return this.selected.indexOf(asset.id) != -1;
		},
		save: function (asset) {
			this.resource.save({id: asset.id}, {asset: asset}).then(function () {
				this.load();
				this.$notify('Asset saved.');
			});
		},
		status: function (status) {

			var assets = this.getSelected();

			assets.forEach(function (asset) {
				asset.status = status;
			});

			this.resource.save({id: 'bulk'}, {assets: assets}).then(function () {
				this.load();
				this.$notify('Assets saved.');
			});
		},
		toggleStatus: function (asset) {
			asset.status = asset.status === 1 ? 2 : 1;
			this.save(asset);
		},
		remove: function () {

			this.resource.delete({id: 'bulk'}, {ids: this.selected}).then(function () {
				this.load();
				this.$notify('Assets deleted.');
			});
		},
		copy: function () {

			if (!this.selected.length) {
				return;
			}

			this.resource.save({id: 'copy'}, {ids: this.selected}).then(function () {
				this.load();
				this.$notify('Assets copied.');
			});
		},
		load: function () {
			this.resource.query({filter: this.config.filter, page: this.config.page}).then(function (res) {

				var data = res.data;

				this.$set('assets', data.assets);
				this.$set('pages', data.pages);
				this.$set('count', data.count);
				this.$set('selected', []);
			});
		},
		getSelected: function () {
			return this.assets.filter(function (asset) {
				return this.selected.indexOf(asset.id) !== -1;
			}, this);
		},
		removeAssets: function () {
			this.resource.delete({id: 'bulk'}, {ids: this.selected}).then(function () {
				this.load();
				this.$notify('Assets(s) deleted.');
			});
		},
		getStatusText: function (asset) {
			return this.statuses[asset.status];
		}
	},
	components: {}
};
Vue.ready(window.assets);
