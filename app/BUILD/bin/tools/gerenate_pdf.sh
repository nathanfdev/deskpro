#!/usr/bin/env bash

if [ "$2" == "" ]; then
    echo "Usage generate_pdf guideSlug rootUrl"
else
    if [[ $2 != http://* ]]; then
        echo "rootUrl is invalid"
    else
        echo "Creating pdf for $1"
        if [ -f var/cache/active_build.txt ]; then
            BUILD_NUMBER=`cat var/cache/active_build.txt`
        else
            BUILD_NUMBER='BUILD'
        fi
        wkhtmltopdf \
            --dpi 300 \
            --margin-right '14mm' \
            --margin-left '14mm' \
            --margin-bottom '25mm' \
            --margin-top '16mm' \
            --footer-html $2/assets/${BUILD_NUMBER}/pub/static/Guides/pdf_footer.html  \
            --outline toc \
            $2/en/guide_pdf/$1 \
            attachments/guides/pdf/$1.pdf
    fi
fi

