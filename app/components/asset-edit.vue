<template>
    <div class="uk-grid pk-grid-large pk-width-sidebar-large uk-form-stacked" data-uk-grid-margin>
        <div class="pk-width-content">
            <div class="uk-form-row">
                <input class="uk-width-1-1 uk-form-large" type="text" name="title" :placeholder="'Enter Title' | trans"
                       v-model="asset.title" v-validate:required>
                <p class="uk-form-help-block uk-text-danger"
                   v-show="form.title.invalid">{{ 'Title cannot be blank.' | trans }}</p>
            </div>
            <div class="uk-form-row" v-if="asset.data.type != 'url'">
                <div class="uk-alert">{{ 'Please do not add script or style tags to the code input field. These will be added automatically.' | trans }}</div>
                <label class="uk-form-label">{{ 'Code' | trans }}</label>
                <div class="uk-form-controls uk-form-controls-text">
                    <v-editor type="code" :value.sync="asset.data.content"></v-editor>
                </div>
            </div>
            <div class="uk-form-row" v-if="asset.data.type == 'url'">
                <label for="form-url" class="uk-form-label">{{ 'URL' | trans }}</label>
                <div class="uk-form-controls">
                    <input id="form-url" class="uk-width-1-1 uk-form-large" type="text" name="url" :placeholder="'Enter URL' | trans"
                           v-model="asset.data.url">
                </div>
            </div>
        </div>
        <div class="pk-width-sidebar">
            <div class="uk-panel">
                <div class="uk-form-row">
                    <label for="form-slug" class="uk-form-label">{{ 'Slug' | trans }}</label>
                    <div class="uk-form-controls">
                        <input id="form-slug" class="uk-width-1-1" type="text" v-model="asset.slug">
                    </div>
                </div>
                <div class="uk-form-row">
                    <label for="form-status" class="uk-form-label">{{ 'Status' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-status" class="uk-width-1-1" v-model="asset.status">
                            <option v-for="(id, status) in data.statuses" :value="id">{{status}}</option>
                        </select>
                    </div>
                </div>
                <div class="uk-form-row">
                    <label for="form-lang" class="uk-form-label">{{ 'Language' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-lang" class="uk-form-width-large" v-model="asset.data.lang">
                            <option value="js">{{ 'JavaScript' | trans }}</option>
                            <option value="css">{{ 'CSS' | trans }}</option>
                        </select>
                    </div>
                </div>
                <div class="uk-form-row">
                    <label for="form-type" class="uk-form-label">{{ 'Type' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-type" class="uk-form-width-large" v-model="asset.data.type">
                            <option value="inline">{{ 'Inline' | trans }}</option>
                            <option value="file">{{ 'Save Content To File' | trans }}</option>
                            <option value="url">{{ 'URL' | trans }}</option>
                        </select>
                    </div>
                </div>
                <div class="uk-form-row" v-if="asset.data.type == 'file'">
                    <label for="form-filename" class="uk-form-label">{{ 'Filename' | trans }}</label>
                    <div class="uk-form-controls">
                        <input id="form-filename" class="uk-width-1-1" type="text" v-model="asset.data.filename">
                    </div>
                </div>
                <div class="uk-form-row" v-if="asset.data.lang == 'js'">
                    <label for="form-execution" class="uk-form-label">{{ 'Execution' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-execution" class="uk-form-width-large" v-model="asset.data.execution">
                            <option value="async" :disabled = "asset.data.type == 'inline'">{{ 'Async' | trans }}</option>
                            <option value="deferred" :disabled = "asset.data.type == 'inline'">{{ 'Deferred' | trans }}</option>
                            <option value="immediately">{{ 'Immediately' | trans }}</option>
                        </select>
                    </div>
                </div>
                <div class="uk-form-row" v-if="asset.data.lang == 'js'">
                    <span class="uk-form-label">{{ 'Load Dependencies' | trans }}</span>
                    <div class="uk-form-controls uk-form-controls-text">
                        <p class="uk-form-controls-condensed">
                            <input id="form-jquery" type="checkbox" v-model="asset.data.dependencies.jquery"> {{ 'jQuery' }}
                        </p>
                        <p class="uk-form-controls-condensed">
                            <input id="form-vuejs" type="checkbox" v-model="asset.data.dependencies.vue"> {{ 'Vue.js' }}
                        </p>
                        <p class="uk-form-controls-condensed">
                            <input id="form-codemirror" type="checkbox" v-model="asset.data.dependencies.codemirror"> {{ 'Codemirror' }}
                        </p>
                        <p class="uk-form-controls-condensed">
                            <input id="form-lodash" type="checkbox" v-model="asset.data.dependencies.lodash"> {{ 'Lodash' }}
                        </p>
                        <p class="uk-form-controls-condensed">
                            <input id="form-marked" type="checkbox" v-model="asset.data.dependencies.marked"> {{ 'Marked' }}
                        </p>
                        <p class="uk-form-controls-condensed">
                            <input id="form-uikit" type="checkbox" v-model="asset.data.dependencies.uikit"> {{ 'Uikit' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

module.exports = {

	props: ['asset', 'data', 'form'],

	section: {
		label: 'Asset'
	}

};

</script>