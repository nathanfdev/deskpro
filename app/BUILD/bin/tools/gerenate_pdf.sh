#!/usr/bin/env bash

if [ "$2" == "" ]; then
    echo "Usage generate_pdf guideSlug rootUrl"
else
    if [[ $2 != http://* ]];
    then
        echo "rootUrl is invalid"
    else
        echo "Creating pdf for $1"
        wkhtmltopdf \
            --dpi 300 \
            --margin-right '14mm' \
            --margin-left '14mm' \
            --margin-bottom '25mm' \
            --margin-top '16mm' \
            --footer-html $2/assets/BUILD/pub/static/Guides/pdf_footer.html  \
            --outline toc \
            $2/en/guide_pdf/$1 \
            www/assets/BUILD/pub/static/Guides/pdf/$1.pdf
    fi
fi

