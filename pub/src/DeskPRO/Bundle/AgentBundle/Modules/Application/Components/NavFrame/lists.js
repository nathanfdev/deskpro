import React from 'react';

export class ListItem extends React.Component {
    render() {
        const {count, label} = this.props;

        return (
            <li>
                {this.renderCount(count)}
                <a href="#" className="item">{label}</a>
                {this.renderChildren()}
            </li>
        );
    }

    renderCount(count) {
        if (!count) {
            return;
        }

        return (
            <div className="list-counter-bucket">
                <a className="list-counter active" href="#">{count}</a>
            </div>
        );
    }

    renderChildren() {
        if (!this.props.children) {
            return;
        }

        return (
            <ul className="with-connectors">
                {this.props.children}
            </ul>
        );
    }
}
