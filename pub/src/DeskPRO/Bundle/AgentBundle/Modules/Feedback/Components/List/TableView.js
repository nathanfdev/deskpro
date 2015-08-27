import React from 'react';
import { connect } from 'redux/react';

@connect(state => state.FeedbackList)

export class TableView extends React.Component {

    constructor(props) {
        super(props);
    }

    render() {
        const {feedback} = this.props;
        return (
            <div className="tickets-tabular">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Votes</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Type</th>
                        <th>Labels</th>
                        <th>Submitter</th>
                    </tr>
                    </thead>
                    <tbody>
                    {feedback.map(feedback =>
                        <tr key={feedback.id}>
                            <td>{feedback.id}</td>
                            <td>{feedback.popularity}</td>
                            <td><a href="#">{feedback.title}</a></td>
                            <td>{feedback.status}</td>
                            <td>{feedback.type}</td>
                            <td></td>
                            <td>{feedback.author_name}</td>
                        </tr>)}
                    </tbody>
                </table>
            </div>
        );
    }
}
