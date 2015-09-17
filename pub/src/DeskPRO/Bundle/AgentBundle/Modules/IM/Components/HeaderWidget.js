import React from 'react';
import Overlay from './Overlay';
import Recent from './Recent';
import * as actions from '../Actions/imListActions';

const HeaderWidget = React.createClass({

    getInitialState: function() {
        return {
            overlayShown: false,
            recentAgents: []//this.props.dispatch(actions.loadRecentAgents())
        };
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
                { this.state.recentAgents.map((agent, index) => <Recent key={index} agent={agent} />)}
                { this.state.overlayShown ? <Overlay/> : null }
            </div>
        );
    }
});

module.exports = HeaderWidget;