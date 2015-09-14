import React from 'react';
import * as actions from '../Actions/imListActions';
import IMOverlay from './Overlay';

const IMButton = React.createClass({

    getInitialState: function() {
        return { overlayShown: false };
    },

    onClick: function() {
        this.setState({ overlayShown: !this.state.overlayShown });
    },

    render: function() {
        return (
            <div className="agent-ims">
                <a href="#" onClick={this.onClick} className="show-more">
                  <span>
                      IMs <i className="fa fa-angle-down"></i>
                  </span>
                </a>
                { this.state.overlayShown ? <IMOverlay/> : null }
            </div>
        );
    }
});

module.exports = IMButton;