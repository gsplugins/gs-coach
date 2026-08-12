<template>
	<div class="gs-containeer-f edit-shortcode-container">

		<div class="shortcode-settings-area-box" v-if="Object.keys(shortcode_settings).length">

			<!-- Settings Panel -->
			<div class="gs-coach-box gs-coach-settings-tab">

				<div class="gs-coach-tab-links--area">
					<ul class="gs-coach--tab-links">
						<li :class="currentTab == 'general_settings' ? 'is-active' : ''">
							<button @click="setSettingsTab('general_settings')">
								<span v-if="show_default_tab_links">{{translation('general-settings')}}</span>
								<span v-else>{{translation('general-settings-short')}}</span>
							</button>
						</li>
						<li :class="currentTab == 'style_settings' ? 'is-active' : ''">
							<button @click="setSettingsTab('style_settings')">
								<span v-if="show_default_tab_links">{{translation('style-settings')}}</span>
								<span v-else>{{translation('style-settings-short')}}</span>
							</button>
						</li>
						<li :class="currentTab == 'query_settings' ? 'is-active' : ''">
							<button @click="setSettingsTab('query_settings')">
								<span v-if="show_default_tab_links">{{translation('query-settings')}}</span>
								<span v-else>{{translation('query-settings-short')}}</span>
							</button>
						</li>
					</ul>
				</div>

				<div class="gs-coach-settings-tab-contents">

					<div class="gscoach--general-settings" v-if="currentTab == 'general_settings'">

						<div class="shortcode-setting--row" v-if="shortcode_text">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="shortcode-input">Copy Shortcode:</label><br>
								</div>

								<div class="gs-col-xs-7">
									<div class="copy-holder--wrapper">
										<input id="shortcode-input" type="text" class="shortcode-input" :value="shortcode_text" readonly>
										<span :class="['copy-holder', copied ? 'copied' : '']" @click.prevent="shortcodeUpdateCopy" v-text="copied ? 'Copied' : 'Copy'"></span>
									</div>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="shortcode_name">{{translation('shortcode-name')}}:</label><br>
								</div>

								<div class="gs-col-xs-7">
									<input type="text" id="shortcode_name" class="bi-input-control" v-model="shortcode_name" :placeholder="translation('shortcode-name')">
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_theme">{{translation('style-theming')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_theme" id="gs_coach_theme" v-model="shortcode_settings.gs_coach_theme" :options="shortcode_options.gs_coach_theme" :placeholder="translation('theme')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('select-preffered-style-theme')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="themes_v2_carousel().includes( shortcode_settings.gs_coach_theme )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="carousel_enabled">{{translation('carousel_enabled')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="carousel_enabled" v-model="shortcode_settings.carousel_enabled" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('carousel_enabled__details')}}</p>
								</div>

							</div>

						</div>

						<template v-if="( themes_v2_carousel().includes( shortcode_settings.gs_coach_theme ) && shortcode_settings.carousel_enabled ) || old_carousel_themes().includes( shortcode_settings.gs_coach_theme )">

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="carousel_autoplay">{{translation('carousel_autoplay')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="carousel_autoplay" v-model="shortcode_settings.carousel_autoplay" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_autoplay__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_autoplay">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="carousel_autoplay_hover_pause">{{translation('carousel_autoplay_hover_pause')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="carousel_autoplay_hover_pause" v-model="shortcode_settings.carousel_autoplay_hover_pause" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_autoplay_hover_pause__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="carousel_loop">{{translation('carousel_loop')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="carousel_loop" v-model="shortcode_settings.carousel_loop" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_loop__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_autoplay">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="carousel_autoplay_speed">{{translation('carousel_autoplay_speed')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="number" class="bi-input-control" id="carousel_autoplay_speed" v-model="shortcode_settings.carousel_autoplay_speed" placeholder="1000" min="0" step="100">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_autoplay_speed__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_autoplay">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="carousel_autoplay_timeout">{{translation('carousel_autoplay_timeout')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="number" class="bi-input-control" id="carousel_autoplay_timeout" v-model="shortcode_settings.carousel_autoplay_timeout" placeholder="2500" min="0" step="100">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_autoplay_timeout__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="carousel_items_to_scroll">{{translation('carousel_items_to_scroll')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input type="number" class="bi-input-control" id="carousel_items_to_scroll" v-model="shortcode_settings.carousel_items_to_scroll" placeholder="1" min="1" step="1">
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_items_to_scroll__details')}}</p>
									</div>

								</div>

							</div>

						</template>


						<div class="shortcode-setting--row" v-if="!shortcode_settings.carousel_enabled && themes_v2_filter().includes( shortcode_settings.gs_coach_theme )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="filter_enabled">{{translation('filter_enabled')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="filter_enabled" v-model="shortcode_settings.filter_enabled" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('filter_enabled__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.filter_enabled && !shortcode_settings.carousel_enabled && themes_v2_filter().includes( shortcode_settings.gs_coach_theme )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_filter_type">{{translation('filter_type')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_filter_type" id="gs_coach_filter_type" v-model="shortcode_settings.gs_coach_filter_type" :options="shortcode_options.gs_coach_filter_type" :placeholder="translation('filter_type')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('filter_type__details')}}</p>
								</div>

							</div>

						</div>

						
						<div class="shortcode-setting--row" v-if="is_display_pagination_settings()">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="enable_pagination">{{translation('enable_pagination')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="enable_pagination" v-model="shortcode_settings.enable_pagination" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('enable_pagination__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.enable_pagination && is_display_pagination_settings()">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="pagination_type">{{translation('pagination_type')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="pagination_type" id="pagination_type" v-model="shortcode_settings.pagination_type" :options="shortcode_options.pagination_type" :placeholder="translation('pagination_type')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('pagination_type__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.enable_pagination && is_display_pagination_settings() && displayCondition( shortcode_settings.pagination_type, [ 'load-more-button', 'load-more-scroll' ] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="initial_items">{{translation('initial_items')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="initial_items" v-model="shortcode_settings.initial_items" placeholder="6">
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('initial_items__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.enable_pagination && is_display_pagination_settings() && displayCondition( shortcode_settings.pagination_type, [ 'normal-pagination', 'ajax-pagination' ] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="coach_per_page">{{translation('coach_per_page')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="coach_per_page" v-model="shortcode_settings.coach_per_page" placeholder="6">
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('coach_per_page__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.enable_pagination && is_display_pagination_settings() && shortcode_settings.pagination_type == 'load-more-button'">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="load_per_click">{{translation('load_per_click')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="load_per_click" v-model="shortcode_settings.load_per_click" placeholder="6">
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('load_per_click__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.enable_pagination && is_display_pagination_settings() && shortcode_settings.pagination_type == 'load-more-scroll'">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="per_load">{{translation('per_load')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="per_load" v-model="shortcode_settings.per_load" placeholder="6">
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('per_load__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.enable_pagination && is_display_pagination_settings() && shortcode_settings.pagination_type == 'load-more-button'">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="load_button_text">{{translation('load_button_text')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="text" class="bi-input-control" id="load_button_text" v-model="shortcode_settings.load_button_text" placeholder="Load More">
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('load_button_text__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="displayCondition( shortcode_settings.gs_coach_theme, [ 'gs-grid-style-one', 'gs-grid-style-two', 'gs-grid-style-three', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-grid-style-six', 'gs-grid-style-seven', 'gs-grid-style-eight', 'gs-grid-style-nine', ] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="link_preview_image">{{translation('link_preview_image')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="link_preview_image" v-model="shortcode_settings.link_preview_image" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('preview_enabled__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-settings--row">
							<template>

								<div class="gs-she-tabs--buttons m-b-20">
									<button :class="active_column_tab == 'desktop' && 'is-active'" @click="active_column_tab = 'desktop'">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="3" y="4" width="18" height="12" stroke="currentColor" stroke-width="2"/>
											<rect x="9" y="18" width="6" height="2" stroke="currentColor" stroke-width="2"/>
											<line x1="9" y1="16" x2="15" y2="16" stroke="currentColor" stroke-width="2"/>
										</svg>
									</button>
									<button :class="active_column_tab == 'tablet' && 'is-active'" @click="active_column_tab = 'tablet'">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="6" y="3" width="12" height="18" stroke="currentColor" stroke-width="2"/>
											<circle cx="12" cy="19" r="1" fill="currentColor"/>
										</svg>
									</button>
									<button :class="active_column_tab == 'large-mobile' && 'is-active'" @click="active_column_tab = 'large-mobile'">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="3" y="8" width="18" height="8" stroke="currentColor" stroke-width="2"/>
											<circle cx="5" cy="12" r="1" fill="currentColor"/>
										</svg>
									</button>
									<button :class="active_column_tab == 'mobile' && 'is-active'" @click="active_column_tab = 'mobile'">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="8" y="2" width="8" height="20" stroke="currentColor" stroke-width="2"/>
											<circle cx="12" cy="18" r="1" fill="currentColor"/>
										</svg>
									</button>
								</div>

							</template>
						</div>

						<div class="shortcode-setting--row" v-if="active_column_tab === 'desktop' && !displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_cols">{{translation('columns')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_cols" id="gs_coach_cols" v-model="shortcode_settings.gs_coach_cols" :options="shortcode_options.gs_coach_cols" :placeholder="translation('column')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('select-number-of-coach-columns')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="active_column_tab === 'tablet' && !displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_cols_tablet">{{translation('columns_tablet')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_cols_tablet" id="gs_coach_cols_tablet" v-model="shortcode_settings.gs_coach_cols_tablet" :options="shortcode_options.gs_coach_cols_tablet" :placeholder="translation('columns_tablet')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('columns_tablet_details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="active_column_tab === 'large-mobile' && !displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_cols_mobile_portrait">{{translation('columns_mobile_portrait')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_cols_mobile_portrait" id="gs_coach_cols_mobile_portrait" v-model="shortcode_settings.gs_coach_cols_mobile_portrait" :options="shortcode_options.gs_coach_cols_mobile_portrait" :placeholder="translation('columns_mobile_portrait')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('columns_mobile_portrait_details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="active_column_tab === 'mobile' && !displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_cols_mobile">{{translation('columns_mobile')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_cols_mobile" id="gs_coach_cols_mobile" v-model="shortcode_settings.gs_coach_cols_mobile" :options="shortcode_options.gs_coach_cols_mobile" :placeholder="translation('columns_mobile')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('columns_mobile_details')}}</p>
								</div>

							</div>

						</div>
						
						<div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme13', 'gs_tm_drawer2', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme25'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_name_is_linked">{{translation('gs_coach_name_is_linked')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_name_is_linked" v-model="shortcode_settings.gs_coach_name_is_linked" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs_coach_name_is_linked__details')}}</p>
								</div>

							</div>

						</div>



						<template v-if="shortcode_settings.gs_coach_name_is_linked && !displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme13', 'gs_tm_drawer2', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme25'] )">

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="gs_coach_link_type">{{translation('gs_coach_link_type')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="gs_coach_link_type" id="gs_coach_link_type" v-model="shortcode_settings.gs_coach_link_type" :options="shortcode_options.gs_coach_link_type" :placeholder="translation('column')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area" v-if="shortcode_settings.gs_coach_link_type == 'drawer' && shortcode_settings.carousel_enabled" style="display: block;">
										<p class="bi-text-help">Drawer will not work while carousel is enabled or on carousel only themes.</p>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area" v-else>
										<p class="bi-text-help">{{translation('gs_coach_link_type__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_coach_link_type == 'popup'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="popup_style">{{translation('popup_style')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="popup_style" id="popup_style" v-model="shortcode_settings.popup_style" :options="shortcode_options.popup_style" :placeholder="translation('popup_style')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('popup_style__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_coach_link_type == 'panel'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="panel_style">{{translation('panel_style')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="panel_style" id="panel_style" v-model="shortcode_settings.panel_style" :options="shortcode_options.panel_style" :placeholder="translation('panel_style')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('panel_style__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.gs_coach_link_type == 'drawer' && !shortcode_settings.carousel_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="drawer_style">{{translation('drawer_style')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="drawer_style" id="drawer_style" v-model="shortcode_settings.drawer_style" :options="shortcode_options.drawer_style" :placeholder="translation('drawer_style')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('drawer_style__details')}}</p>
									</div>

								</div>

							</div>

						</template>



						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_thumbnail_sizes">{{translation('gs_coach_thumbnail_sizes')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_thumbnail_sizes" id="gs_coach_thumbnail_sizes" v-model="shortcode_settings.gs_coach_thumbnail_sizes" :options="shortcode_options.gs_coach_thumbnail_sizes" :placeholder="translation('gs_coach_thumbnail_sizes')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs_coach_thumbnail_sizes_details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme13', 'gs_tm_drawer2', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_name">{{translation('coach-name')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_name" v-model="shortcode_settings.gs_coach_name" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('show-or-hide-coach-name')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme13', 'gs_tm_drawer2', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_role">{{translation('coach-designation')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_role" v-model="shortcode_settings.gs_coach_role" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('show-or-hide-coach-designation')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme23', 'gs_tm_theme_custom_10', 'gs_tm_theme13', 'gs_tm_drawer2', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme20', 'gs_tm_grid2', 'gs_tm_theme22', 'gs_tm_theme11'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_details">{{translation('coach-details')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_details" v-model="shortcode_settings.gs_coach_details" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('show-or-hide-coach-details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_grid2', 'gs_tm_theme_custom_10', 'gs_tm_theme11', 'gs_tm_theme20', 'gs_tm_theme21', 'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme25'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_connect">{{translation('social-connection')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_connect" v-model="shortcode_settings.gs_coach_connect" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('show-or-hide-coach-social-connections')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="! displayCondition( shortcode_settings.gs_coach_theme, no_ribbon_themes() )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="display_ribbon">{{translation('display-ribbon')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="display_ribbon" v-model="shortcode_settings.display_ribbon" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('show-or-hide-ribbon__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.display_ribbon && ! displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme3'] ) && ! displayCondition( shortcode_settings.gs_coach_theme, no_ribbon_themes() )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_ribbon_style">{{translation('ribbon_style')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_ribbon_style" id="gs_coach_ribbon_style" v-model="shortcode_settings.gs_coach_ribbon_style" :options="shortcode_options.gs_coach_ribbon_styles"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('ribbon_style__details')}}</p>
								</div>

							</div>

						</div>

						<!-- <div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme7', 'gs_tm_theme9', 'gs_tm_theme12', 'gs_tm_theme21', 'gs_tm_theme22', 'gs_tm_theme25'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_pagination">{{translation('pagination')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_pagination" v-model="shortcode_settings.gs_coach_pagination" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('show-or-hide-coach-paginations')}}</p>
								</div>

							</div>

						</div> -->

						<div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_srch_by_name">{{translation('instant-search-by-name')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_srch_by_name" v-model="shortcode_settings.gs_coach_srch_by_name" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('show-or-hide-instant-search-applicable-for-theme-9')}}</p>
								</div>

							</div>

						</div>

						<!-- <div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_srch_by_company">{{translation('gs-coach-srch-by-company')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_srch_by_company" v-model="shortcode_settings.gs_coach_srch_by_company" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs-coach-srch-by-company--details')}}</p>
								</div>

							</div>

						</div> -->

						<!-- <div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_srch_by_zip">{{translation('gs-coach-srch-by-zip')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_srch_by_zip" v-model="shortcode_settings.gs_coach_srch_by_zip" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs-coach-srch-by-zip--details')}}</p>
								</div>

							</div>

						</div> -->

						<div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme19', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_srch_by_tag">{{translation('gs-coach-srch-by-tag')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_srch_by_tag" v-model="shortcode_settings.gs_coach_srch_by_tag" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs-coach-srch-by-tag--details')}}</p>
								</div>

							</div>

						</div>

						<template v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_desig">{{translation('filter-by-designation')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_desig" v-model="shortcode_settings.gs_coach_filter_by_desig" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-designation--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_location">{{translation('filter-by-location')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_location" v-model="shortcode_settings.gs_coach_filter_by_location" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-location--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_language">{{translation('filter-by-language')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_language" v-model="shortcode_settings.gs_coach_filter_by_language" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-language--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_gender">{{translation('filter-by-gender')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_gender" v-model="shortcode_settings.gs_coach_filter_by_gender" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-gender--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_speciality">{{translation('filter-by-speciality')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_speciality" v-model="shortcode_settings.gs_coach_filter_by_speciality" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-speciality--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_extra_one">{{translation('filter-by-extra-one')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_extra_one" v-model="shortcode_settings.gs_coach_filter_by_extra_one" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-extra-one--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_extra_two">{{translation('filter-by-extra-two')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_extra_two" v-model="shortcode_settings.gs_coach_filter_by_extra_two" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-extra-two--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_extra_three">{{translation('filter-by-extra-three')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_extra_three" v-model="shortcode_settings.gs_coach_filter_by_extra_three" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-extra-three--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_extra_four">{{translation('filter-by-extra-four')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_extra_four" v-model="shortcode_settings.gs_coach_filter_by_extra_four" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-extra-four--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_filter_by_extra_five">{{translation('filter-by-extra-five')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_filter_by_extra_five" v-model="shortcode_settings.gs_coach_filter_by_extra_five" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('filter-by-extra-five--des')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_enable_clear_filters">{{translation('enable-clear-filters')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_enable_clear_filters" v-model="shortcode_settings.gs_coach_enable_clear_filters" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('enable-clear-filters--help')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_coach_enable_multi_select">{{translation('enable-multi-select')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="gs_coach_enable_multi_select" v-model="shortcode_settings.gs_coach_enable_multi_select" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('enable-multi-select--help')}}</p>
									</div>

								</div>

							</div>

						</template>

						<div class="shortcode-setting--row" v-if="
							shortcode_settings.gs_coach_enable_multi_select &&
							(
								displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
								( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
							)
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_coach_multi_select_ellipsis">{{translation('multi-select-ellipsis')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_coach_multi_select_ellipsis" v-model="shortcode_settings.gs_coach_multi_select_ellipsis" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('multi-select-ellipsis--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_filter_all_enabled">{{translation('filter-all-enabled')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_filter_all_enabled" v-model="shortcode_settings.gs_filter_all_enabled" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('filter-all-enabled--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme12', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="enable_child_cats">{{translation('enable-child-cats')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="enable_child_cats" v-model="shortcode_settings.enable_child_cats" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('enable-child-cats--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme7', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="enable_scroll_animation">{{translation('enable-scroll-animation')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="enable_scroll_animation" v-model="shortcode_settings.enable_scroll_animation" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('enable-scroll-animation--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="fitler_all_text">{{translation('fitler-all-text')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="text" class="bi-input-control" id="fitler_all_text" v-model="shortcode_settings.fitler_all_text" :placeholder="translation('fitler-all-text')">
								</div>
								
								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('fitler-all-text--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme22', 'gs_tm_theme24', 'gs_tm_theme25'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coach_filter_columns">{{translation('gs_coach_filter_columns')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coach_filter_columns" id="gs_coach_filter_columns" v-model="shortcode_settings.gs_coach_filter_columns" :options="shortcode_options.gs_coach_filter_columns"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="!displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_grid2', 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme21', 'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme25'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_desc_allow_html">{{translation('gs-desc-allow-html')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_desc_allow_html" v-model="shortcode_settings.gs_desc_allow_html" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs-desc-allow-html--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="! shortcode_settings.gs_desc_allow_html && !displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_grid2', 'gs_tm_theme_custom_10', 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme21', 'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme25'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_details_contl">{{translation('details-control')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="gs_tm_details_contl" v-model="shortcode_settings.gs_tm_details_contl" :placeholder="translation('details-control')">
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('define-maximum-number-of-characters')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_desc_scroll_contrl">{{translation('gs-desc-scroll-contrl')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="gs_desc_scroll_contrl" v-model="shortcode_settings.gs_desc_scroll_contrl" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs-desc-scroll-contrl--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.gs_desc_scroll_contrl">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_max_scroll_height">{{translation('gs-max-scroll-height')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="gs_max_scroll_height" v-model="shortcode_settings.gs_max_scroll_height" placeholder="200">
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('gs-max-scroll-height--help')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="is_popup_enabled">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_coaches_pop_clm">{{translation('popup-column')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_coaches_pop_clm" id="gs_coaches_pop_clm" v-model="shortcode_settings.gs_coaches_pop_clm" :options="shortcode_options.gs_coaches_pop_clm" :placeholder="translation('popup-column')"></input-select>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('set-column-for-popup')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="
							displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme9', 'gs_tm_theme12'] ) ||
							( displayCondition( shortcode_settings.gs_coach_theme, themes_v2_filter() ) && shortcode_settings.filter_enabled )
						">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_filter_cat_pos">{{translation('filter-category-position')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_tm_filter_cat_pos" id="gs_tm_filter_cat_pos" v-model="shortcode_settings.gs_tm_filter_cat_pos" :options="shortcode_options.gs_tm_filter_cat_pos" :placeholder="translation('filter-category-position')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme19'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="panel">{{translation('panel')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="panel" id="panel" v-model="shortcode_settings.panel" :options="shortcode_options.panel" :placeholder="translation('panel')"></input-select>
								</div>

							</div>

						</div>

						<template v-if="enabled_plugins.includes('advanced-custom-fields')">
							
							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="show_acf_fields">{{translation('show-acf-fields')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="show_acf_fields" v-model="shortcode_settings.show_acf_fields" offLabel="Off" onLabel="On"></input-toggle>
									</div>

								</div>

							</div>
							
							<div class="shortcode-setting--row" v-if="shortcode_settings.show_acf_fields">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="acf_fields_position">{{translation('acf-fields-position')}}:</label>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="acf_fields_position" id="acf_fields_position" v-model="shortcode_settings.acf_fields_position" :options="shortcode_options.acf_fields_position" :placeholder="translation('acf_fields_position')"></input-select>
									</div>

								</div>

							</div>

						</template>

					</div>

					<div class="gscoach--style-settings" v-if="currentTab == 'style_settings'">

						<template v-if="( themes_v2_carousel().includes( shortcode_settings.gs_coach_theme ) && shortcode_settings.carousel_enabled ) || old_carousel_themes().includes( shortcode_settings.gs_coach_theme )">

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="carousel_navs_enabled">{{translation('carousel_navs_enabled')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="carousel_navs_enabled" v-model="shortcode_settings.carousel_navs_enabled" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_navs_enabled__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="carousel_dots_enabled">{{translation('carousel_dots_enabled')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-5">
										<input-toggle class="m-t-6" name="carousel_dots_enabled" v-model="shortcode_settings.carousel_dots_enabled" offLabel="Off" onLabel="On"></input-toggle>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_dots_enabled__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_navs_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="carousel_navs_style">{{translation('carousel_navs_style')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="carousel_navs_style" id="carousel_navs_style" v-model="shortcode_settings.carousel_navs_style" :options="shortcode_options.carousel_navs_style" :placeholder="translation('carousel_navs_style')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_navs_style__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_dots_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="carousel_dots_style">{{translation('carousel_dots_style')}}:</label>
										<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="carousel_dots_style" id="carousel_dots_style" v-model="shortcode_settings.carousel_dots_style" :options="shortcode_options.carousel_dots_style" :placeholder="translation('carousel_dots_style')"></input-select>
									</div>

									<div class="gs-col-xs-12 bi-text-help--area">
										<p class="bi-text-help">{{translation('carousel_dots_style__details')}}</p>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_navs_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_slider_nav_color">{{translation('gs_slider_nav_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="gs_slider_nav_color" v-model="shortcode_settings.gs_slider_nav_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_navs_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_slider_nav_bg_color">{{translation('gs_slider_nav_bg_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="gs_slider_nav_bg_color" v-model="shortcode_settings.gs_slider_nav_bg_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_navs_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_slider_nav_hover_color">{{translation('gs_slider_nav_hover_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="gs_slider_nav_hover_color" v-model="shortcode_settings.gs_slider_nav_hover_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_navs_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_slider_nav_hover_bg_color">{{translation('gs_slider_nav_hover_bg_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="gs_slider_nav_hover_bg_color" v-model="shortcode_settings.gs_slider_nav_hover_bg_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_dots_enabled">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_slider_dot_color">{{translation('gs_slider_dot_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="gs_slider_dot_color" v-model="shortcode_settings.gs_slider_dot_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="shortcode_settings.carousel_dots_enabled && shortcode_settings.carousel_dots_style == 'style-one'">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="gs_slider_dot_hover_color">{{translation('gs_slider_dot_hover_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="gs_slider_dot_hover_color" v-model="shortcode_settings.gs_slider_dot_hover_color"></input-color>
									</div>

								</div>

							</div>

						</template>

						<template v-if="( themes_v2_filter().includes( shortcode_settings.gs_coach_theme ) && shortcode_settings.filter_enabled ) || old_filter_themes().includes( shortcode_settings.gs_coach_theme )">
							
							<div class="shortcode-setting--row">
								<div class="gs-roow row-20">

									<div class="gs-col-xs-5">
										<label class="m-t-10" for="filter_style">{{translation('filter_style')}}:</label>
									</div>

									<div class="gs-col-xs-7">
										<input-select key="filter_style" id="filter_style" v-model="shortcode_settings.filter_style" :options="shortcode_options.filter_style" :placeholder="translation('filter_style')"></input-select>
									</div>

								</div>
							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="filter_text_color">{{translation('filter_text_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="filter_text_color" v-model="shortcode_settings.filter_text_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="filter_active_text_color">{{translation('filter_active_text_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="filter_active_text_color" v-model="shortcode_settings.filter_active_text_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="['style-four', 'style-five'].includes( shortcode_settings.filter_style )">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="filter_bg_color">{{translation('filter_bg_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="filter_bg_color" v-model="shortcode_settings.filter_bg_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="! ['style-one'].includes( shortcode_settings.filter_style )">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="filter_active_bg_color">{{translation('filter_active_bg_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="filter_active_bg_color" v-model="shortcode_settings.filter_active_bg_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="['default', 'style-three'].includes( shortcode_settings.filter_style )">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="filter_border_color">{{translation('filter_border_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="filter_border_color" v-model="shortcode_settings.filter_border_color"></input-color>
									</div>

								</div>

							</div>

							<div class="shortcode-setting--row" v-if="['default', 'style-three'].includes( shortcode_settings.filter_style )">

								<div class="gs-roow row-20">

									<div class="gs-col-xs-7">
										<label class="m-t-10" for="filter_active_border_color">{{translation('filter_active_border_color')}}:</label>
									</div>

									<div class="gs-col-xs-5">
										<input-color id="filter_active_border_color" v-model="shortcode_settings.filter_active_border_color"></input-color>
									</div>

								</div>

							</div>

						</template>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="image_filter">{{translation('image_filter')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="image_filter" id="image_filter" v-model="shortcode_settings.image_filter" :options="shortcode_options.image_filter" :placeholder="translation('image_filter')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="hover_image_filter">{{translation('hover_image_filter')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="hover_image_filter" id="hover_image_filter" v-model="shortcode_settings.hover_image_filter" :options="shortcode_options.hover_image_filter" :placeholder="translation('hover_image_filter')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_m_fz">{{translation('name-font-size')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="gs_tm_m_fz" v-model="shortcode_settings.gs_tm_m_fz" :placeholder="translation('font-size')">
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_m_fntw">{{translation('name-font-weight')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_tm_m_fntw" id="gs_tm_m_fntw" v-model="shortcode_settings.gs_tm_m_fntw" :options="shortcode_options.gs_tm_m_fntw" :placeholder="translation('font-weight')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_m_fnstyl">{{translation('name-font-style')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_tm_m_fnstyl" id="gs_tm_m_fnstyl" v-model="shortcode_settings.gs_tm_m_fnstyl" :options="shortcode_options.gs_tm_m_fnstyl" :placeholder="translation('font-style')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_mname_color">{{translation('name-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_mname_color" v-model="shortcode_settings.gs_tm_mname_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="description_color">{{translation('description-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="description_color" v-model="shortcode_settings.description_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="info_color">{{translation('info-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="info_color" v-model="shortcode_settings.info_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="info_icon_color">{{translation('info-icon-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="info_icon_color" v-model="shortcode_settings.info_icon_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="description_link_color">{{translation('description-link-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="description_link_color" v-model="shortcode_settings.description_link_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="! displayCondition( shortcode_settings.gs_coach_theme, this.no_bg_color_themes() )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="tm_bg_color">{{translation('tm-bg-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="tm_bg_color" v-model="shortcode_settings.tm_bg_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="! displayCondition( shortcode_settings.gs_coach_theme, this.no_bg_color_themes() )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="tm_bg_color_hover">{{translation('tm-bg-color-hover')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="tm_bg_color_hover" v-model="shortcode_settings.tm_bg_color_hover"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="displayCondition( shortcode_settings.gs_coach_theme, this.info_bg_color_themes() )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_info_background">{{translation('info-bg-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_info_background" v-model="shortcode_settings.gs_tm_info_background"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme19'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_mname_background">{{translation('name-bg-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_mname_background" v-model="shortcode_settings.gs_tm_mname_background"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_grid2'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_tooltip_background">{{translation('tooltip-bg-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_tooltip_background" v-model="shortcode_settings.gs_tm_tooltip_background"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme19'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_hover_icon_background">{{translation('hover-icon-bg-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_hover_icon_background" v-model="shortcode_settings.gs_tm_hover_icon_background"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="shortcode_settings.display_ribbon && ! displayCondition( shortcode_settings.gs_coach_theme, ['gs_tm_theme3'] )">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_ribon_color">{{translation('ribon-background-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_ribon_color" v-model="shortcode_settings.gs_tm_ribon_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_role_fz">{{translation('role-font-size')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input type="number" class="bi-input-control" id="gs_tm_role_fz" v-model="shortcode_settings.gs_tm_role_fz" :placeholder="translation('font-size')">
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_role_fntw">{{translation('role-font-weight')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_tm_role_fntw" id="gs_tm_role_fntw" v-model="shortcode_settings.gs_tm_role_fntw" :options="shortcode_options.gs_tm_role_fntw" :placeholder="translation('font-weight')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="gs_tm_role_fnstyl">{{translation('role-font-style')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="gs_tm_role_fnstyl" id="gs_tm_role_fnstyl" v-model="shortcode_settings.gs_tm_role_fnstyl" :options="shortcode_options.gs_tm_role_fnstyl" :placeholder="translation('font-style')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_role_color">{{translation('role-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_role_color" v-model="shortcode_settings.gs_tm_role_color"></input-color>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row" v-if="is_popup_enabled">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="gs_tm_arrow_color">{{translation('popup-arrow-color')}}:</label>
								</div>

								<div class="gs-col-xs-5">
									<input-color id="gs_tm_arrow_color" v-model="shortcode_settings.gs_tm_arrow_color"></input-color>
								</div>

							</div>

						</div>

					</div>

					<div class="gscoach--query-settings" v-if="currentTab == 'query_settings'">

						<div class="shortcode-setting--row" v-if="!shortcode_settings.enable_pagination">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="num">{{translation('coaches')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-7">
									<input type="text" class="bi-input-control" id="num" v-model="shortcode_settings.num" :placeholder="translation('coaches')">
								</div>
								
								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('set-max-coach-numbers-you-want-to-show')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="orderby">{{translation('order-by')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="orderby" id="orderby" v-model="shortcode_settings.orderby" :options="shortcode_options.orderby" :placeholder="translation('order-by')"></input-select>
								</div>

							</div>

						</div>
						
						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="order">{{translation('order')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="order" id="order" v-model="shortcode_settings.order" :options="shortcode_options.order" :placeholder="translation('order')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="taxonomy_orderby">{{translation('taxonomy-order-by')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="taxonomy_orderby" id="taxonomy_orderby" v-model="shortcode_settings.taxonomy_orderby" :options="shortcode_options.taxonomy_orderby" :placeholder="translation('taxonomy-order-by')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-5">
									<label class="m-t-10" for="taxonomy_order">{{translation('taxonomy-order')}}:</label>
								</div>

								<div class="gs-col-xs-7">
									<input-select key="taxonomy_order" id="taxonomy_order" v-model="shortcode_settings.taxonomy_order" :options="shortcode_options.order" :placeholder="translation('taxonomy-order')"></input-select>
								</div>

							</div>

						</div>

						<div class="shortcode-setting--row">

							<div class="gs-roow row-20">

								<div class="gs-col-xs-7">
									<label class="m-t-10" for="taxonomy_hide_empty">{{translation('taxonomy_hide_empty')}}:</label>
									<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
								</div>

								<div class="gs-col-xs-5">
									<input-toggle class="m-t-6" name="taxonomy_hide_empty" v-model="shortcode_settings.taxonomy_hide_empty" offLabel="Off" onLabel="On"></input-toggle>
								</div>

								<div class="gs-col-xs-12 bi-text-help--area">
									<p class="bi-text-help">{{translation('taxonomy_hide_empty__details')}}</p>
								</div>

							</div>

						</div>

						<div class="shortcode-settings--row">
							<template>

								<div class="gs-she-tabs--buttons">
									<button :class="active_query_tab == 'include' && 'is-active'"
										@click="active_query_tab = 'include'">Include</button>
									<button :class="active_query_tab == 'exclude' && 'is-active'"
										@click="active_query_tab = 'exclude'">Exclude</button>
								</div>

								<template v-if="active_query_tab == 'include'">

									<div v-if="this.tax_settings.enable_group_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="group">{{ translation('group') }}:</label>
												<button class="gscoach-show--info"><i
														class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select id="group" key="group" v-model="shortcode_settings.group"
													:options="shortcode_options.group" :placeholder="translation('group')"
													multiple></input-select>
											</div>

											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{ translation('group__help') }}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_tag_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="tags">{{ translation('tags')
													}}:</label>
												<button class="gscoach-show--info"><i
														class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select id="tag" key="tag"
													v-model="shortcode_settings.tag"
													:options="shortcode_options.tag"
													:placeholder="translation('tags')" multiple></input-select>
											</div>

											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{ translation('tags__help') }}</p>
											</div>

										</div>

									</div>
									
									<div v-if="this.tax_settings.enable_language_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="language">{{translation('language')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="language" id="language" v-model="shortcode_settings.language" :options="shortcode_options.language" :placeholder="translation('language')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('language--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_location_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="location">{{translation('location')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="location" id="location" v-model="shortcode_settings.location" :options="shortcode_options.location" :placeholder="translation('location')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('location--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_specialty_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="specialty">{{translation('specialty')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="specialty" id="specialty" v-model="shortcode_settings.specialty" :options="shortcode_options.specialty" :placeholder="translation('specialty')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('specialty--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_gender_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="gender">{{translation('gender')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="gender" id="gender" v-model="shortcode_settings.gender" :options="shortcode_options.gender" :placeholder="translation('gender')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('gender--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_one_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="include_extra_one">{{translation('include_extra_one')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="include_extra_one" id="include_extra_one" v-model="shortcode_settings.include_extra_one" :options="shortcode_options.extra_one" :placeholder="translation('include_extra_one')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('include_extra_one--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_two_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="include_extra_two">{{translation('include_extra_two')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="include_extra_two" id="include_extra_two" v-model="shortcode_settings.include_extra_two" :options="shortcode_options.extra_two" :placeholder="translation('include_extra_two')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('include_extra_two--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_three_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="include_extra_three">{{translation('include_extra_three')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="include_extra_three" id="include_extra_three" v-model="shortcode_settings.include_extra_three" :options="shortcode_options.extra_three" :placeholder="translation('include_extra_three')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('include_extra_three--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_four_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="include_extra_four">{{translation('include_extra_four')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="include_extra_four" id="include_extra_four" v-model="shortcode_settings.include_extra_four" :options="shortcode_options.extra_four" :placeholder="translation('include_extra_four')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('include_extra_four--details')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_five_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="include_extra_five">{{translation('include_extra_five')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="include_extra_five" id="include_extra_five" v-model="shortcode_settings.include_extra_five" :options="shortcode_options.extra_five" :placeholder="translation('include_extra_five')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('include_extra_five--details')}}</p>
											</div>

										</div>

									</div>

								</template>

								<template v-if="active_query_tab == 'exclude'">

									<div v-if="this.tax_settings.enable_group_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_group">{{ translation('exclude_group')
													}}:</label>
												<button class="gscoach-show--info"><i
														class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select id="exclude_group" key="exclude_group"
													v-model="shortcode_settings.exclude_group"
													:options="shortcode_options.group"
													:placeholder="translation('exclude_group')" multiple></input-select>
											</div>

											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{ translation('exclude_group__help') }}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_tag_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_tags">{{ translation('exclude_tags')
													}}:</label>
												<button class="gscoach-show--info"><i
														class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select id="exclude_tags" key="exclude_tags"
													v-model="shortcode_settings.exclude_tags"
													:options="shortcode_options.tag"
													:placeholder="translation('exclude_tags')" multiple></input-select>
											</div>

											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{ translation('exclude_tags__help') }}</p>
											</div>

										</div>

									</div>

									
									<div v-if="this.tax_settings.enable_language_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_language">{{translation('exclude_language')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_language" id="exclude_language" v-model="shortcode_settings.exclude_language" :options="shortcode_options.language" :placeholder="translation('exclude_language')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_language__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_location_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_location">{{translation('exclude_location')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_location" id="exclude_location" v-model="shortcode_settings.exclude_location" :options="shortcode_options.location" :placeholder="translation('exclude_location')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_location__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_specialty_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_specialty">{{translation('exclude_specialty')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_specialty" id="exclude_specialty" v-model="shortcode_settings.exclude_specialty" :options="shortcode_options.specialty" :placeholder="translation('exclude_specialty')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_specialty__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_gender_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_gender">{{translation('exclude_gender')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_gender" id="exclude_gender" v-model="shortcode_settings.exclude_gender" :options="shortcode_options.gender" :placeholder="translation('exclude_gender')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_gender__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_one_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="include_extra_one">{{translation('exclude_extra_one')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_extra_one" id="exclude_extra_one" v-model="shortcode_settings.exclude_extra_one" :options="shortcode_options.extra_one" :placeholder="translation('exclude_extra_one')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_extra_one__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_two_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_extra_two">{{translation('exclude_extra_two')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_extra_two" id="exclude_extra_two" v-model="shortcode_settings.exclude_extra_two" :options="shortcode_options.extra_two" :placeholder="translation('exclude_extra_two')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_extra_two__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_three_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_extra_three">{{translation('exclude_extra_three')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_extra_three" id="exclude_extra_three" v-model="shortcode_settings.exclude_extra_three" :options="shortcode_options.extra_three" :placeholder="translation('exclude_extra_three')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_extra_three__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_four_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_extra_four">{{translation('exclude_extra_four')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_extra_four" id="exclude_extra_four" v-model="shortcode_settings.exclude_extra_four" :options="shortcode_options.extra_four" :placeholder="translation('exclude_extra_four')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_extra_four__help')}}</p>
											</div>

										</div>

									</div>

									<div v-if="this.tax_settings.enable_extra_five_tax == 'on'" class="shortcode-setting--row">

										<div class="gs-roow row-20">

											<div class="gs-col-xs-5">
												<label class="m-t-10" for="exclude_extra_five">{{translation('exclude_extra_five')}}:</label>
												<button class="gscoach-show--info"><i class="zmdi zmdi-help-outline"></i></button>
											</div>

											<div class="gs-col-xs-7">
												<input-select key="exclude_extra_five" id="exclude_extra_five" v-model="shortcode_settings.exclude_extra_five" :options="shortcode_options.extra_five" :placeholder="translation('exclude_extra_five')" multiple></input-select>
											</div>
											
											<div class="gs-col-xs-12 bi-text-help--area">
												<p class="bi-text-help">{{translation('exclude_extra_five__help')}}</p>
											</div>

										</div>

									</div>

								</template>

							</template>
						</div>

					</div>

					<!-- Save buttons -->
					<div class="m-t-20">
						<button class="btn btn-md btn-brand" @click.prevent.stop="saveOrUpdateShortcode"><i class="zmdi zmdi-floppy"></i><span>{{translation('save-shortcode')}}</span></button>
					</div>

				</div>

			</div>
			
			<!-- Right Panel -->
			<div class="gs-coach-right-panel">

				<!-- Preview Panel -->
				<div class="preview-shortcode-iframe-wrapper">
					<iframe v-if="previewReady" id="gscoach-shortcode-preview-iframe" :src='getSiteURL()+"/?gscoach_shortcode_preview=" + getPreviewTempID( true )' frameborder="0"></iframe>
				</div>

			</div>

		</div>

	</div>
</template>

<script>

	import notify from '../includes/notify';

	export default {

		data() {

			return {

				currentTab: 'general_settings',

				pageTitle: this.translation('create-shortcode'),

				pageDescription: this.translation('create-a-new-shortcode-and'),

				previewReady: false,

				id: null,

				copied: false,

				shortcode_name: null,
				
				active_query_tab: 'include',

				active_column_tab: 'desktop',

				shortcode_settings: {},

				tax_settings: {},

				shortcode_options: {},

				enabled_plugins: _gscoach_data.enabled_plugins,

				show_default_tab_links: true

			}

		},

		mounted() {

			this.stopWatcher = false;
			
			this.previewTempID = this.getPreviewTempID();

			this.id = ( this.$route.params.id ) ? this.$route.params.id: null;

			if ( this.id ) {
				this.pageTitle = this.translation('edit-shortcode'),
				this.fetchShortcode( this.id );
			} else {
				this.setInitialSettings();
				this.generatePreview();
			}

			this.initHelpText();

			this.$watch( 'shortcode_settings', function() {
				
				if ( this.previewHandler ) clearTimeout( this.previewHandler );

				this.previewHandler = setTimeout( () => {
					this.generatePreview();
				}, 200 );

			}, {
				deep: true
			});

			this.copyShortcodeToClipboard();

			this.update_show_default_tab_links();

			jQuery(window).on('resize', this.update_show_default_tab_links);

			var body = document.body, html = document.documentElement;
			this.docHeight = Math.max( body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight );

			this.update_editor_height();

			jQuery(window).on('scroll', this.update_editor_height);

			this.tax_settings = this._getTaxSettings();


			this.resetLinkTypes();

		},

		computed: {
			
			shortcode_text() {
				if ( ! this.id ) return '';
				return '[gscoach id='+this.id+']';
			},

			is_popup_enabled() {
				
				if ( ! this.shortcode_settings.gs_coach_name_is_linked ) return false;

				if ( this.shortcode_settings.gs_coach_link_type == 'popup' ) return true;

				if ( this.shortcode_settings.gs_coach_link_type == 'single_page' ) return false;

				return this.displayCondition( this.shortcode_settings.gs_coach_theme, ['gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme12'] );

			}

		},

		beforeDestroy() {

			jQuery(window).off('resize', this.update_show_default_tab_links);
			jQuery(window).off('scroll', this.update_editor_height);

		},

		methods: {

			is_display_pagination_settings(){
				if( this.shortcode_settings.filter_enabled && this.shortcode_settings.gs_coach_filter_type === 'normal-filter' ){
					return false;
				}

				if( this.shortcode_settings.carousel_enabled ){
					return false;
				}

				return true;
			},

			no_ribbon_themes(){
				return ['gs-coach-circle-one', 'gs-coach-circle-two', 'gs-coach-circle-three', 'gs-coach-circle-four', 'gs-coach-circle-five', 'gs_tm_theme2', 'gs-coach-circle-seven', 'gs-coach-circle-eight', 'gs-coach-circle-nine', 'gs-coach-circle-ten'];
			},

			old_carousel_themes() {
				return ['gs_tm_theme7'];
			},

			old_filter_themes() {
				return ['gs_tm_theme9', 'gs_tm_theme12'];
			},

			no_bg_color_themes() {
				return ['gs-grid-style-one', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-coach-circle-one', 'gs-coach-circle-two', 'gs-coach-circle-three', 'gs-coach-circle-four', 'gs-coach-circle-five', 'gs-coach-circle-seven', 'gs-coach-circle-eight', 'gs-coach-circle-nine', 'gs-coach-circle-ten', 'gs-coach-horizontal-one', 'gs-coach-horizontal-three', 'gs-coach-flip-one', 'gs-coach-flip-two', 'gs-coach-flip-three', 'gs-coach-flip-four', 'gs-coach-flip-five', 'gs-coach-table-one', 'gs-coach-table-two', 'gs-coach-table-three', 'gs-coach-table-four', 'gs-coach-table-five', 'gs-coach-list-style-four', 'gs-coach-list-style-five', 'gs-coach-list-style-six', 'gs-coach-list-style-seven', 'gs-coach-list-style-eight', 'gs-coach-list-style-nine', 'gs_tm_theme1', 'gs_tm_theme2', 'gs_tm_theme7', 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme10', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme14', 'gs_tm_theme15', 'gs_tm_theme16', 'gs_tm_theme21', 'gs_tm_theme21_dense', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme22', 'gs_tm_theme23', 'gs_tm_theme24', 'gs_tm_theme25', 'gs_tm_grid2', 'gs_tm_drawer2'];
			},

			info_bg_color_themes() {
				return ['gs-grid-style-one', 'gs-grid-style-four', 'gs-grid-style-five', 'gs-coach-circle-three', 'gs-coach-circle-four', 'gs-coach-horizontal-one', 'gs-coach-horizontal-three', 'gs-coach-flip-one', 'gs-coach-flip-two', 'gs-coach-flip-three', 'gs-coach-flip-four', 'gs-coach-flip-five', 'gs_tm_drawer2', 'gs_tm_theme22', 'gs_tm_theme1', 'gs_tm_theme2', 'gs_tm_theme7', 'gs_tm_theme8', 'gs_tm_theme9', 'gs_tm_theme11', 'gs_tm_theme12', 'gs_tm_theme13', 'gs_tm_theme19', 'gs_tm_theme20', 'gs_tm_theme25'];
			},

			update_editor_height() {

				if ( window.matchMedia("(max-width: 960px)").matches ) return;
				
				let scrollTop = jQuery(window).scrollTop();
				let screenScrolled = scrollTop + window.innerHeight;

				let $container = jQuery('.gs-coach-app-view-container');
				let $area_box = jQuery('.shortcode-settings-area-box');

				var header_height = 68;
				var defaultHeight = 160;

				if ( scrollTop <= header_height ) {
					$area_box.css({
						height: `calc(100vh - ${defaultHeight-scrollTop}px)`
					});
				}

				if ( screenScrolled < this.docHeight ) {
					$container.css('margin-top', '0px');
				} else {
					return;
				}

				if ( scrollTop > header_height ) {
					$container.css({
						marginTop: ( scrollTop - header_height ) + 'px'
					});
				}

			},

			update_show_default_tab_links() {

				if ( window.matchMedia("(min-width: 1460px)").matches ) {
					this.show_default_tab_links = true;
				}

				if ( window.matchMedia("(max-width: 1459px)").matches ) {
					this.show_default_tab_links = false;
				}

				if ( window.matchMedia("(max-width: 960px)").matches ) {
					this.show_default_tab_links = true;
				}

				if ( window.matchMedia("(max-width: 500px)").matches ) {
					this.show_default_tab_links = false;
				}

			},

			shortcodeUpdateCopy() {

				this.copied = true;

				var handler = setTimeout(() => {
					clearTimeout(handler);
					this.copied = false;
				}, 4000);
			},

			displayCondition( field, values ) {

				if ( values.includes(field) ) return true;

				return false;

			},

			setInitialSettings() {

				let shortcode_options = this._getShortcodeOptions();

				for ( let option_group in shortcode_options ) {
					if( ! Array.isArray(shortcode_options[option_group]) ) {
						shortcode_options[option_group] = [];
					}
					if ( shortcode_options[option_group].some( item => item.pro ) ) {
						shortcode_options[option_group] = shortcode_options[option_group].map( item => {
							if ( item.pro ) item.disabled = true;
							return item;
						});
					}
				}

				this.$set( this, 'shortcode_options', shortcode_options );

				this.setShortcodeSettings( this._getShortcodeSettings() );

			},

			getPreviewTempID() {
				if ( this.previewTempID ) return this.previewTempID;
				return 'gscoach_' + Math.random().toString(36).substr(2, 9);
			},

			setSettingsTab( val ) {

				this.currentTab = val;

			},

			getShortcodeName() {
				return this.shortcode_name;
			},

			getShortcodeSettings( json = false ) {
				
				let shortcode_settings  = this.nonReactive( this.shortcode_settings );
				let group               = shortcode_settings.group;
				let tag                 = shortcode_settings.tag;
				let location            = shortcode_settings.location;
				let language            = shortcode_settings.language;
				let specialty           = shortcode_settings.specialty;
				let gender              = shortcode_settings.gender;
				let include_extra_one   = shortcode_settings.include_extra_one;
				let include_extra_two   = shortcode_settings.include_extra_two;
				let include_extra_three = shortcode_settings.include_extra_three;
				let include_extra_four  = shortcode_settings.include_extra_four;
				let include_extra_five  = shortcode_settings.include_extra_five;
				let exclude_group       = shortcode_settings.exclude_group;
				let exclude_tags        = shortcode_settings.exclude_tags;
				let exclude_language    = shortcode_settings.exclude_language;
				let exclude_location    = shortcode_settings.exclude_location;
				let exclude_specialty   = shortcode_settings.exclude_specialty;
				let exclude_gender      = shortcode_settings.exclude_gender;
				let exclude_extra_one   = shortcode_settings.exclude_extra_one;
				let exclude_extra_two   = shortcode_settings.exclude_extra_two;
				let exclude_extra_three = shortcode_settings.exclude_extra_three;
				let exclude_extra_four  = shortcode_settings.exclude_extra_four;
				let exclude_extra_five  = shortcode_settings.exclude_extra_five;

				if ( group && typeof group == 'object' && group.length ) {
					shortcode_settings.group = group.join(',');
				}

				if ( tag && typeof tag == 'object' && tag.length ) {
					shortcode_settings.tag = tag.join(',');
				}

				if ( location && typeof location == 'object' && location.length ) {
					shortcode_settings.location = location.join(',');
				}

				if ( language && typeof language == 'object' && language.length ) {
					shortcode_settings.language = language.join(',');
				}

				if ( specialty && typeof specialty == 'object' && specialty.length ) {
					shortcode_settings.specialty = specialty.join(',');
				}

				if ( gender && typeof gender == 'object' && gender.length ) {
					shortcode_settings.gender = gender.join(',');
				}

				if ( include_extra_one && typeof include_extra_one == 'object' && include_extra_one.length ) {
					shortcode_settings.include_extra_one = include_extra_one.join(',');
				}

				if ( include_extra_two && typeof include_extra_two == 'object' && include_extra_two.length ) {
					shortcode_settings.include_extra_two = include_extra_two.join(',');
				}

				if ( include_extra_three && typeof include_extra_three == 'object' && include_extra_three.length ) {
					shortcode_settings.include_extra_three = include_extra_three.join(',');
				}

				if ( include_extra_four && typeof include_extra_four == 'object' && include_extra_four.length ) {
					shortcode_settings.include_extra_four = include_extra_four.join(',');
				}

				if ( include_extra_five && typeof include_extra_five == 'object' && include_extra_five.length ) {
					shortcode_settings.include_extra_five = include_extra_five.join(',');
				}
				
				if ( exclude_group && typeof exclude_group == 'object' && exclude_group.length ) {
					shortcode_settings.exclude_group = exclude_group.join(',');
				}
				
				if ( exclude_tags && typeof exclude_tags == 'object' && exclude_tags.length ) {
					shortcode_settings.exclude_tags = exclude_tags.join(',');
				}
				
				if ( exclude_language && typeof exclude_language == 'object' && exclude_language.length ) {
					shortcode_settings.exclude_language = exclude_language.join(',');
				}
				
				if ( exclude_location && typeof exclude_location == 'object' && exclude_location.length ) {
					shortcode_settings.exclude_location = exclude_location.join(',');
				}
				
				if ( exclude_specialty && typeof exclude_specialty == 'object' && exclude_specialty.length ) {
					shortcode_settings.exclude_specialty = exclude_specialty.join(',');
				}
				
				if ( exclude_gender && typeof exclude_gender == 'object' && exclude_gender.length ) {
					shortcode_settings.exclude_gender = exclude_gender.join(',');
				}
				
				if ( exclude_extra_one && typeof exclude_extra_one == 'object' && exclude_extra_one.length ) {
					shortcode_settings.exclude_extra_one = exclude_extra_one.join(',');
				}
				
				if ( exclude_extra_two && typeof exclude_extra_two == 'object' && exclude_extra_two.length ) {
					shortcode_settings.exclude_extra_two = exclude_extra_two.join(',');
				}
				
				if ( exclude_extra_three && typeof exclude_extra_three == 'object' && exclude_extra_three.length ) {
					shortcode_settings.exclude_extra_three = exclude_extra_three.join(',');
				}
				
				if ( exclude_extra_four && typeof exclude_extra_four == 'object' && exclude_extra_four.length ) {
					shortcode_settings.exclude_extra_four = exclude_extra_four.join(',');
				}
				
				if ( exclude_extra_five && typeof exclude_extra_five == 'object' && exclude_extra_five.length ) {
					shortcode_settings.exclude_extra_five = exclude_extra_five.join(',');
				}

				for ( let field in shortcode_settings ) {
					if ( typeof shortcode_settings[field] === "boolean" ) {
						shortcode_settings[field] = this.convertBooleanToString( shortcode_settings[field] );
					}
				}

				if ( json ) return JSON.stringify(shortcode_settings);

				return shortcode_settings;

			},

			setShortcodeSettings( settings ) {

				for ( let field in settings ) {

					if ( typeof settings[field] === "string" && (settings[field] === 'on' || settings[field] === 'off' ) ) {
						settings[field] = this.convertStringToBoolean( settings[field] );
					}

				}

				this.shortcode_settings = Object.assign( {}, this.shortcode_settings, settings );

				if ( settings.group && typeof settings.group == 'string' ) {
					this.shortcode_settings.group = settings.group.split(',').map(Number);
				}

				if ( settings.tag && typeof settings.tag == 'string' ) {
					this.shortcode_settings.tag = settings.tag.split(',').map(Number);
				}

				if ( settings.location && typeof settings.location == 'string' ) {
					this.shortcode_settings.location = settings.location.split(',').map(Number);
				}

				if ( settings.language && typeof settings.language == 'string' ) {
					this.shortcode_settings.language = settings.language.split(',').map(Number);
				}

				if ( settings.specialty && typeof settings.specialty == 'string' ) {
					this.shortcode_settings.specialty = settings.specialty.split(',').map(Number);
				}

				if ( settings.gender && typeof settings.gender == 'string' ) {
					this.shortcode_settings.gender = settings.gender.split(',').map(Number);
				}

				if ( settings.include_extra_one && typeof settings.include_extra_one == 'string' ) {
					this.shortcode_settings.include_extra_one = settings.include_extra_one.split(',').map(Number);
				}

				if ( settings.include_extra_two && typeof settings.include_extra_two == 'string' ) {
					this.shortcode_settings.include_extra_two = settings.include_extra_two.split(',').map(Number);
				}

				if ( settings.include_extra_three && typeof settings.include_extra_three == 'string' ) {
					this.shortcode_settings.include_extra_three = settings.include_extra_three.split(',').map(Number);
				}

				if ( settings.include_extra_four && typeof settings.include_extra_four == 'string' ) {
					this.shortcode_settings.include_extra_four = settings.include_extra_four.split(',').map(Number);
				}

				if ( settings.include_extra_five && typeof settings.include_extra_five == 'string' ) {
					this.shortcode_settings.include_extra_five = settings.include_extra_five.split(',').map(Number);
				}
				
				if ( settings.exclude_group && typeof settings.exclude_group == 'string' ) {
					this.shortcode_settings.exclude_group = settings.exclude_group.split(',').map(Number);
				}
				
				if ( settings.exclude_tag && typeof settings.exclude_tag == 'string' ) {
					this.shortcode_settings.exclude_tag = settings.exclude_tag.split(',').map(Number);
				}
				
				if ( settings.exclude_language && typeof settings.exclude_language == 'string' ) {
					this.shortcode_settings.exclude_language = settings.exclude_language.split(',').map(Number);
				}
				
				if ( settings.exclude_location && typeof settings.exclude_location == 'string' ) {
					this.shortcode_settings.exclude_location = settings.exclude_location.split(',').map(Number);
				}
				
				if ( settings.exclude_specialty && typeof settings.exclude_specialty == 'string' ) {
					this.shortcode_settings.exclude_specialty = settings.exclude_specialty.split(',').map(Number);
				}
				
				if ( settings.exclude_gender && typeof settings.exclude_gender == 'string' ) {
					this.shortcode_settings.exclude_gender = settings.exclude_gender.split(',').map(Number);
				}
				
				if ( settings.exclude_extra_one && typeof settings.exclude_extra_one == 'string' ) {
					this.shortcode_settings.exclude_extra_one = settings.exclude_extra_one.split(',').map(Number);
				}
				
				if ( settings.exclude_extra_two && typeof settings.exclude_extra_two == 'string' ) {
					this.shortcode_settings.exclude_extra_two = settings.exclude_extra_two.split(',').map(Number);
				}
				
				if ( settings.exclude_extra_three && typeof settings.exclude_extra_three == 'string' ) {
					this.shortcode_settings.exclude_extra_three = settings.exclude_extra_three.split(',').map(Number);
				}
				
				if ( settings.exclude_extra_four && typeof settings.exclude_extra_four == 'string' ) {
					this.shortcode_settings.exclude_extra_four = settings.exclude_extra_four.split(',').map(Number);
				}
				
				if ( settings.exclude_extra_five && typeof settings.exclude_extra_five == 'string' ) {
					this.shortcode_settings.exclude_extra_five = settings.exclude_extra_five.split(',').map(Number);
				}

				this.$forceUpdate();

			},

			fetchShortcode( shortcode_id ) {

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'GET',
					cache: false,
					data: {
						action: 'gscoach_get_shortcode',
						id: shortcode_id
					}
				})
				.done( response => {

					let shortcode = response.data;

					this.shortcode_name = shortcode.shortcode_name;
					this.setInitialSettings();
					this.setShortcodeSettings( shortcode.shortcode_settings );
					this.generatePreview();

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			saveOrUpdateShortcode() {

				this.id ? this.updateShortcode() : this.saveShortcode();

			},

			saveShortcode() {

				let shortcode_name = this.getShortcodeName();
				let shortcode_settings = this.getShortcodeSettings();

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gscoach_create_shortcode',
						_wpnonce: this.getWPNonce(),
						shortcode_name: shortcode_name,
						shortcode_settings: shortcode_settings
					}
				})
				.done( response => {

					if ( response.success ) {

						notify({
							message: response.data.message,
							type: 'success'
						});

						let id = response.data.shortcode_id;

						return this.$router.push(`/shortcode/${id}`);
						
					}

					notify({
						message: response.data,
						type: 'info'
					});

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			generatePreview() {
				this.saveTempSettings();
			},

			updatePreview() {
				let frame = jQuery(this.$el).find('#gscoach-shortcode-preview-iframe');
				if ( frame.length ) frame[0].contentWindow.location.reload();
			},

			saveTempSettings() {

				let shortcode_name = this.getShortcodeName();
				let shortcode_settings = this.getShortcodeSettings();

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gscoach_temp_save_shortcode_settings',
						_wpnonce: this.getWPNonce(),
						temp_key: this.getPreviewTempID(),
						shortcode_settings: shortcode_settings
					}
				})
				.done( response => {

					if ( ! response.success ) return;

					if ( this.previewReady ) {
						this.updatePreview();
					} else {
						this.previewReady = true;
					}

				})
				.error( response => {

					this.notifyError( response );

				});

			},

			updateShortcode() {

				let shortcode_name = this.getShortcodeName();
				let shortcode_settings = this.getShortcodeSettings();

				jQuery.ajax({
					url: this.getAjaxURL(),
					type: 'POST',
					cache: false,
					data: {
						action: 'gscoach_update_shortcode',
						_wpnonce: this.getWPNonce(),
						id: this.id,
						shortcode_name: shortcode_name,
						shortcode_settings: shortcode_settings
					}
				})
				.done( response => {

					if ( response.success ) {

						return notify({
							message: response.data.message,
							type: 'success'
						});

					}

					notify({
						message: response.data,
						type: 'info'
					});
				})
				.error( response => {

					this.notifyError( response );

				});

			},

			resetLinkTypes() {
				if ( this.shortcode_settings.gs_coach_name_is_linked && this.shortcode_settings.gs_coach_link_type == 'drawer' ) {
					this.shortcode_settings.gs_coach_link_type = 'default';
				}
			},

			disableCarouselFilers() {
				if ( this.shortcode_settings.gs_coach_name_is_linked && this.shortcode_settings.gs_coach_link_type == 'drawer' ) {
					this.shortcode_settings.carousel_enabled = false;
					this.shortcode_settings.filter_enabled = false;
				}
			}

		},

		watch: {

			'shortcode_settings.carousel_enabled': function() {
				if ( this.shortcode_settings.carousel_enabled === true ) {
					this.shortcode_settings.filter_enabled = false;
					this.resetLinkTypes();
				}
			},

			'shortcode_settings.filter_enabled': function() {
				if ( this.shortcode_settings.filter_enabled === true ) {
					this.resetLinkTypes();
				}
			},

			'shortcode_settings.gs_coach_name_is_linked': function() {
				if ( this.shortcode_settings.filter_enabled || this.shortcode_settings.carousel_enabled ) {
					this.resetLinkTypes();
				}
			},

			'shortcode_settings.gs_coach_link_type': function() {
				if ( this.shortcode_settings.filter_enabled || this.shortcode_settings.carousel_enabled ) {
					this.disableCarouselFilers();
				}
			}

		}

	}

</script>