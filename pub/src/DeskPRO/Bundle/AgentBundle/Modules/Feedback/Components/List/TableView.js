import React from 'react';
import {Row} from './Row';
import {TableHeader} from './TableHeader';

export class TableView extends React.Component {


    render() {
        const {feedback, sortTable} = this.props;
        return (
            <div className="tickets-tabular">
                <table>
                    <TableHeader sortTable={sortTable.bind(this)}/>
                    <tbody>
                    {feedback.map(feedback => <Row feedback={feedback}/>)}
                    </tbody>
                </table>
            </div>
        );
    }
}