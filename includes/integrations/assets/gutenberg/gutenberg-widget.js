( function( wp, React, $ ) {

    const { data, blocks, serverSideRender: ServerSideRender } = wp;
    const { __ } = wp.i18n;
    const { registerBlockType } = blocks;
    const { withSelect } = data;

    var interval, interval_count = 0;

    const BlockServerRenderScript = function() {

        if ( interval ) clearInterval( interval );

        interval_count = 0;

        interval = setInterval(function() {
            
            $(document).trigger( 'gscoach:scripts:reprocess' );
            if ( interval && interval_count > 100 ) clearInterval( interval );
            interval_count ++;

        }, 200);

    }

    const BlockDisplay = function({ setAttributes, attributes, className }) {

        let shortcodeID = attributes.shortcode;

        function updateShortcodeID( event ) {

            setAttributes({
                shortcode: event.target.value
            });

        }

        function getShortcodeOptions() {

            return gs_coach_block.gs_coach_shortcodes.map(function( item ) {
                return <option value={item.id} key={item.id}>{ item.shortcode_name }</option>
            });

        }

        BlockServerRenderScript();

        return <div className='gscoach-coaches--block'>

            <div className='gscoach-coaches--toolbar'>

                <label>{ gs_coach_block.select_shortcode }</label>

                <select onChange={updateShortcodeID} value={shortcodeID}>
                    { getShortcodeOptions() }
                </select>

                <p className='gs-coach-block--des'>

                    <span>
                        { gs_coach_block.edit_description_text }
                        <a href={gs_coach_block.edit_link + shortcodeID} target='_blank'>{ gs_coach_block.edit_link_text }</a>
                    </span>

                    <span>
                        { gs_coach_block.create_description_text }
                        <a href={gs_coach_block.create_link} target='_blank'>{ gs_coach_block.create_link_text }</a>
                    </span>

                </p>

            </div>

            <ServerSideRender className={className} block='gscoach/shortcodes' attributes={ attributes } />

        </div>
    }

    registerBlockType('gscoach/shortcodes', {

        title: __( 'GS Coach', 'gscoach' ),
        description: __( 'Display your coaches using a GS Coach shortcode.', 'gscoach' ),
        icon: 'groups',
        category: 'widgets',
        keywords: [ 'coach', 'coaches', 'team', 'shortcode' ],
        example: { attributes: {} },
        supports: {
            align: ['wide', 'full']
        },
        attributes: {
            shortcode: {
                type: 'string',
                default: gs_coach_block.gs_coach_shortcodes[0] ? gs_coach_block.gs_coach_shortcodes[0].id : ''
            },
            align: {
                type: 'string',
                default: 'wide'
            }
        },
        edit: withSelect( () => {} )( BlockDisplay )

    });

}( window.wp, window.React, jQuery ));
