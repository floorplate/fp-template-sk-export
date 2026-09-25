---
id: 376c131b-5bfc-4b80-a644-bba6f19b3e76
blueprint: availability
title: '6th Floor'
text_columns:
  -
    id: fpdemo2n
    column_text: '24,800 RSF'
    type: text_column
    enabled: true
  -
    id: fpdemo2o
    column_text: 'Available Q3'
    type: text_column
    enabled: true
text_content:
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Example listing with two detail columns. Titles and columns are plain text; the description below is rich text.'
  -
    type: set
    attrs:
      id: fpdemo2p
      values:
        type: button_types
        button_types:
          -
            id: fpdemo2q
            button_text: 'Download Brochure (PDF)'
            download_asset: downloads/demo/sample-brochure.pdf
            type: download_button
            enabled: true
        alignment: left
        text_color: accent
---
