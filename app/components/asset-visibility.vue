<template>
    <div class="uk-form-horizontal">
        <div class="uk-form-row">
            <span class="uk-form-label">Pages</span>
            <div class="uk-form-controls uk-form-controls-text">
                <input-tree :active.sync="asset.nodes"></input-tree>
            </div>
        </div>
    </div>
</template>

<script>
module.exports = {

	section: {
		label: 'Visibility',
		priority: 100
	},

	props: ['asset', 'form']

}
</script>