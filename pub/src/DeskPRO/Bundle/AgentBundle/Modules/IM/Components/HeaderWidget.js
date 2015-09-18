import React from 'react';
import { connect } from 'react-redux';
import Overlay from './Overlay';
import Recent from './Recent';
import * as actions from '../Actions/imListActions';

@connect(state => ({
    recentAgents: state.IM.list.recentAgents
}))
export default class HeaderWidget extends React.Component {

    constructor(props) {
        super(props);
        this.props.dispatch(actions.loadRecentAgents());
        this.state = {
            overlayShown: false
        };
    }

    onClick() {
        const newState = {
            overlayShown: !this.state.overlayShown
        };
        this.setState(newState);
    }

    render() {
        return (
            <div className="agent-ims">
                <a href="#" onClick={this.onClick.bind(this)} className="show-more">
                  <span>
                      IMs <i className="fa fa-angle-down"></i>
                  </span>
                </a>
                { this.props.recentAgents.map((agent, index) => <Recent key={index} agent={agent} />)}
                { this.state.overlayShown ? <Overlay/> : null }
            </div>
        );
    }
}