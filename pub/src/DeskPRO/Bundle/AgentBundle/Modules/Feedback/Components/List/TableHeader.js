import React from 'react';

export class TableHeader extends React.Component {

    render() {
        const {sortTable} = this.props;
        return (
            <thead>
            <tr>
                <th>
                    <a href="#" onClick={sortTable.bind(this, 'id')} title="Click to sort by ID">
                        ID
                    </a>
                </th>
                <th>
                    <a href="#" onClick={sortTable.bind(this, 'num_ratings')}>
                        Votes
                    </a>
                </th>
                <th>
                    <a href="#" onClick={sortTable.bind(this, 'title')}>
                        Title
                    </a>
                </th>
                <th>
                    <a href="#" onClick={sortTable.bind(this, 'status')}>
                        Status
                    </a>
                </th>
                <th>
                    <a href="#" onClick={sortTable.bind(this, 'category')}>
                        Type
                    </a>
                </th>
                <th>Labels</th>
                <th>
                    <a href="#" onClick={sortTable.bind(this, 'author_name')}>
                        Submitter
                    </a>
                </th>
            </tr>
            </thead>
        );
    }
}