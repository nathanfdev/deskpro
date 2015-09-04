import React from 'react';

export class Options extends React.Component {
    render() {
        const fields = [
            {name: 'id', label: 'ID'},
            {name: 'status', label: 'Status'},
            {name: 'hidden_status', label: 'Hidden status'},
            {name: 'status_category', label: 'Status category'},
            {name: 'title', label: 'Status category'},
            {name: 'author_name', label: 'Submitter'},
            {name: 'language_id', label: 'Lang'},
            {name: 'type', label: 'Type'},
            {name: 'slug', label: 'Slug'},
            {name: 'date_created', label: 'Created'},
            {name: 'date_published', label: 'Published'},
            {name: 'view_count', label: 'Views'},
            {name: 'total_rating', label: 'Rating'},
            {name: 'num_rating', label: 'Votes'},
            {name: 'num_comments', label: 'Comments'},
            {name: 'validating', label: 'Validating'},
            {name: 'popularity', label: 'Popularity'},
            {name: 'content', label: 'Content'},
            {name: 'custom_category', label: 'Category'}
        ];

        return (
            <select multiple>
                {fields.map((field, index) =>
                    <option key={index}>{field.label}</option>)}
            </select>
        );
    }
}
