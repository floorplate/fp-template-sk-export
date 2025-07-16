---
id: 20704273-5182-47d9-abaf-21f3530571d5
blueprint: page
title: Amenities
content_blocks:
  -
    id: mb9pc1tv
    text_color: light
    text_content:
      -
        type: heading
        attrs:
          textAlign: center
          font-weight: font-extralight
          level: 1
        content:
          -
            type: text
            text: AMENITIES
      -
        type: heading
        attrs:
          textAlign: center
          font-weight: light
          level: 6
        content:
          -
            type: text
            text: 'Designed to enhance productivity and comfort'
    type: intro_text
    enabled: true
    spacing_bottom_override: extra-large
  -
    id: mbi3vjnj
    main_media:
      -
        id: mbi453nb
        basic_image: amentiyhero.jpg
        type: basic_image
        enabled: true
    type: full_screen_media
    enabled: true
    mobile_override_media:
      -
        id: mbi48u8q
        basic_image: mobileamenitiescrop.jpg
        type: basic_image
        enabled: true
  -
    id: mbgpmcod
    background_color_override: '#FFF'
    text_color: dark
    text_content:
      -
        type: heading
        attrs:
          textAlign: center
          font-weight: font-extralight
          level: 4
        content:
          -
            type: text
            text: 'Downtown has steadily been emerging as a '
          -
            type: text
            marks:
              -
                type: btsSpan
                attrs:
                  class: text-secondary-text-color
            text: 'revitalized and dynamic'
          -
            type: text
            text: ' part of Jacksonville, and 1 Independent Square is in the middle of it all. With a new generation demanding a lifestyle that values working and playing in a convenient, condensed area, the appeal of a building adjacent to the Riverwalk, along with several on-site amenities, is more than you could hope for.'
      -
        type: set
        attrs:
          id: mbi3fovr
          values:
            type: layout_spacer
      -
        type: set
        attrs:
          id: mbi3epma
          values:
            type: button_types
            button_types:
              -
                id: mbi3eqmp
                button_text: 'Find Out More'
                button_link: 'entry::20704273-5182-47d9-abaf-21f3530571d5'
                open_in_new_tab: false
                type: no_outline_button
                enabled: true
            alignment: center
            text_color: secondary
      -
        type: paragraph
        attrs:
          textAlign: center
    type: intro_text
    enabled: true
  -
    id: mbl0thby
    type: anchor_nav
    enabled: true
    anchor_background_color: '#F0F3F6'
    active_item_background_color: '#FFFFFF'
    text_color: '#576B7D'
  -
    id: mbi7ip3u
    text_color: dark
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          font-weight: font-extralight
          level: 3
        content:
          -
            type: text
            text: 'MODERNIZED ATRIUM'
          -
            type: hardBreak
          -
            type: text
            text: LOBBY
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'The property’s 4-story atrium provides an exhilarating dynamic to your work day and adds to the spacious feel of this office opportunity.'
    carousel_images:
      - availabilitiesdesktop.jpg
      - amentiyhero.jpg
      - heroimageskyline.jpg
      - locationaerialdesktop.jpg
    image_side: right
    pagination_color: '#1D1D1D'
    type: amenity_carousel
    enabled: true
    anchor_id: 'Modernized Atrium Lobby'
  -
    id: mbi7kdzy
    text_color: dark
    reverse_mobile_stack: false
    center_items_vertically: false
    first_text_content:
      -
        type: heading
        attrs:
          textAlign: left
          font-weight: font-extralight
          level: 3
        content:
          -
            type: text
            text: 'THE RIVER CLUB'
    second_text_content:
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Boasting some of the best skyline views, the River Club (a dining institution for over 50 years) offers members access to world-class dining in a truly beautiful setting. Rivaling the stunning views are the interior’s elegant furnishings of glistening chandeliers, warm woods, and rich fabrics. The additional presence of updated conference technology, including drop-down projection screens and wireless internet capabilities, makes this an ideal site for a variety of business luncheons, meetings and more.'
    type: dual_column_text_blocks
    enabled: true
    spacing_top_override: large
    spacing_bottom_override: small
    first_anchor_id: 'The River Club'
    anchor_id: 'River Club'
  -
    id: mbi8zsi7
    image_one: riverone.jpg
    image_two: rivertwo.jpg
    type: dual_image_columns
    enabled: true
  -
    id: mbi7zgwi
    text_color: dark
    text_content:
      -
        type: heading
        attrs:
          textAlign: left
          font-weight: font-extralight
          level: 3
        content:
          -
            type: text
            text: 'GOURMET DELI / COFEE SHOP'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'The Royal Palms Cafe serves breakfast and lunch, offering daily specials and a variety of delicious homemade entrees, salads and sandwiches.'
      -
        type: set
        attrs:
          id: mbi807xm
          values:
            type: button_types
            button_types:
              -
                id: mbi808w6
                button_text: 'View Website'
                button_link: 'https://royalpalmscafeandcatering.com/'
                open_in_new_tab: true
                type: no_outline_button
                enabled: true
            alignment: left
            text_color: secondary
      -
        type: paragraph
        attrs:
          textAlign: left
    carousel_images:
      - cafe2.jpg
      - cafe1.jpg
    image_side: left
    pagination_color: '#1D1D1D'
    type: amenity_carousel
    enabled: true
    anchor_id: 'Gourmet Deli / Coffee Shop'
  -
    id: mbi83cmw
    text_color: dark
    reverse_mobile_stack: false
    center_items_vertically: false
    first_text_content:
      -
        type: heading
        attrs:
          textAlign: left
          font-weight: font-extralight
          level: 3
        content:
          -
            type: text
            text: 'CONFERENCE CENTERS'
    second_text_content:
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: '1 Independent Square has two (2) separate conference centers, both located on the second floor, which are available for rent by our tenants for a variety of meeting and training purposes.'
    type: dual_column_text_blocks
    enabled: true
    spacing_bottom_override: small
    first_anchor_id: 'Conference Centers'
    anchor_id: 'Conference Center'
  -
    id: mbi9allb
    image_one: 7e9556eb4c7185cca40f6c4f70f61ec4aa426c40.jpg
    image_two: 78cdbbdc508c8f0ee927b840b40628445d4c333b.jpg
    type: dual_image_columns
    enabled: true
  -
    id: mbi7rx32
    text_color: dark
    reverse_mobile_stack: true
    center_items_vertically: true
    first_text_content:
      -
        type: paragraph
        attrs:
          textAlign: left
          font-weight: light
        content:
          -
            type: image
            attrs:
              src: 'asset::assets::conference.jpg'
              alt: null
    second_text_content:
      -
        type: heading
        attrs:
          textAlign: left
          font-weight: font-extralight
          level: 3
        content:
          -
            type: text
            text: 'THEATER-STYLE AUDITORIUM'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'Available for the exclusive use of our tenants is a 360-seat theater-style seating auditorium, complete with a full presentation stage, projector screen and available wifi.'
    type: dual_column_text_blocks
    enabled: true
    second_anchor_id: 'Theater-style Auditorium'
    anchor_id: 'Theater Style Auditorium'
  -
    id: mbi85py8
    text_color: dark
    reverse_mobile_stack: false
    center_items_vertically: true
    first_text_content:
      -
        type: heading
        attrs:
          textAlign: left
          font-weight: font-extralight
          level: 3
        content:
          -
            type: text
            text: 'BRANCH BANKING'
      -
        type: paragraph
        attrs:
          textAlign: left
        content:
          -
            type: text
            text: 'A full-service teller facility, mortgage assistance, safety deposit boxes, and an ATM are available for your convenience.'
    second_text_content:
      -
        type: paragraph
        attrs:
          textAlign: left
          font-weight: light
        content:
          -
            type: image
            attrs:
              src: 'asset::assets::fargo.jpg'
              alt: null
    type: dual_column_text_blocks
    enabled: true
    anchor_id: 'Branch Banking'
  -
    id: mb14nknr
    contact_sections:
      -
        id: mb14nlkt
        section_title:
          -
            type: paragraph
            attrs:
              textAlign: left
            content:
              -
                type: text
                text: Leasing
        section_contacts:
          -
            id: mb14nqim
            name: 'BILLY KUNTZ'
            title: 'First Vice President'
            type: contact
            enabled: true
            text_content:
              -
                type: heading
                attrs:
                  textAlign: left
                  font-weight: font-extralight
                  level: 4
                content:
                  -
                    type: text
                    text: 'BILLY KUNTZ'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    text: 'First Vice President'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    marks:
                      -
                        type: link
                        attrs:
                          href: 'tel:904 630 6350'
                          rel: null
                          target: null
                          title: null
                    text: '904 630 6350'
                  -
                    type: hardBreak
                  -
                    type: text
                    marks:
                      -
                        type: link
                        attrs:
                          href: 'mailto:billy.kuntz@cbre.com'
                          rel: null
                          target: null
                          title: null
                    text: billy.kuntz@cbre.com
                  -
                    type: hardBreak
          -
            id: mb14nxy7
            name: 'LOU NUTTER'
            title: 'Senior Vice President'
            type: contact
            enabled: true
            text_content:
              -
                type: heading
                attrs:
                  textAlign: left
                  font-weight: font-extralight
                  level: 4
                content:
                  -
                    type: text
                    text: 'LOU NUTTER'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    text: 'First Vice President'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    marks:
                      -
                        type: link
                        attrs:
                          href: 'tel:9046306341'
                          rel: null
                          target: null
                          title: null
                    text: '904 630 6350'
                  -
                    type: hardBreak
                  -
                    type: text
                    marks:
                      -
                        type: link
                        attrs:
                          href: 'mailto:louis.nutter@cbre.com'
                          rel: null
                          target: null
                          title: null
                    text: louis.nutter@cbre.com
        type: contact_section
        enabled: true
      -
        id: mb14o5oi
        section_title:
          -
            type: paragraph
            content:
              -
                type: text
                text: Management
        section_contacts:
          -
            id: mb14oatb
            name: 'NAME NAME'
            title: Title
            type: contact
            enabled: true
            text_content:
              -
                type: heading
                attrs:
                  textAlign: left
                  font-weight: font-extralight
                  level: 4
                content:
                  -
                    type: text
                    text: 'BILLY KUNTZ'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    text: 'First Vice President'
              -
                type: paragraph
                attrs:
                  textAlign: left
                content:
                  -
                    type: text
                    marks:
                      -
                        type: link
                        attrs:
                          href: 'tel:904 630 6350'
                          rel: null
                          target: null
                          title: null
                    text: '904 630 6350'
                  -
                    type: hardBreak
                  -
                    type: text
                    marks:
                      -
                        type: link
                        attrs:
                          href: 'mailto:billy.kuntz@cbre.com'
                          rel: null
                          target: null
                          title: null
                    text: billy.kuntz@cbre.com
        type: contact_section
        enabled: true
    type: contact_sections
    enabled: true
    text_color: light
updated_by: 8e6db97c-665c-47eb-b476-76d55a45e908
updated_at: 1752524142
nav_style: dark
---
