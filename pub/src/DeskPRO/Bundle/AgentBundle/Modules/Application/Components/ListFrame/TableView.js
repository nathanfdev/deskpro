import React from 'react';

export class TableView extends React.Component {


    render() {
        return (
            <div className="tickets-tabular">
                <table>
                    {this.props.children}
                </table>
            </div>
        );
    }
}