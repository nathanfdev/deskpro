import React from 'react';

export default class Header extends React.Component {
    render() {
        return (
            <header>
                <div className="header-controls">
                    <a href="#"><i className="fa fa-search"></i> Search IM</a>
                    <span className="close">
                      <a href="#" onClick={this.props.handleCloseChat}><i className="fa fa-times"></i></a>
                    </span>
                </div>
                <h1>Your IM with <span>{this.props.target.target_type} {this.props.target.target_id}</span><b className="user-status online"></b></h1>
            </header>
        );
    }
}