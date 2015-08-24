import React from 'react';

export class ListFrame extends React.Component {
    render() {
        return (
            <section className={'feedback-list-frame dp-list-frame'}>
                <div className="feedback-list">
                    {this.props.children}
                </div>
            </section>
        );
    }
}
