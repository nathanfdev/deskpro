import React from 'react';

export default class Overlay extends React.Component {
    render() {
        return (
            <div className="dropdown im-dropdown" id="im-dropdown">
                <header className="dropdown-header">Agent Instant Messages</header>
                <div className="wrapper">
                    <div className="bucket left">
                        <h1>Agents</h1>
                        <div className="show-offline-agents">
                            <input type="checkbox" id="checkbox-name" /><label for="checkbox-name"></label> Show offline agents?
                        </div>

                        <form>
                            <div>
                                <input type="text" placeholder="Filter agents by name" />
                            </div>
                        </form>
                        <div className="im-list-wrapper">
                            <ul className="im-list">
                                <li>
                                    <a href="#">
                                        <span className="chat-avatar chat-user-online" style={{"background-image": "url(./img/avatar.jpg)"}}></span>
                                        <span className="agent">Ben Henley <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <span className="chat-avatar chat-user-online" style={{"background-image": "url(./img/avatar2.png)"}}></span>
                                        <span className="agent">Ben Henleysonn Longnamedguy The Twentyfourth <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <span className="no-avatar"><i className="fa fa-user"></i></span>
                                        <span className="agent">Ben <span className="search-matched-word">Henley</span> <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>

                                <li>
                                    <a href="#" className="offline">
                                        <span className="no-avatar"><i className="fa fa-user"></i></span>
                                        <span className="agent">Ben Henley (offline) <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>

                                <li>
                                    <a href="#" className="offline">
                                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar.jpg)"}}></span>
                                        <span className="agent">Ben Henley (offline) <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>

                                <li>
                                    <a href="#" className="offline">
                                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar.jpg)"}}></span>
                                        <span className="agent">Ben Henley (offline) <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>


                                <li>
                                    <a href="#" className="offline">
                                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar2.png)"}}></span>
                                        <span className="agent">Ben Henley (offline) <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>

                                <li>
                                    <a href="#" className="offline">
                                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar.jpg)"}}></span>
                                        <span className="agent">Ben Henley (offline) <span className="datestamp">2d ago</span></span>
                                    </a>
                                </li>

                                    <li>
                                        <a href="#" className="offline">
                                            <span className="chat-avatar" style={{"background-image": "url(./img/avatar.jpg)"}}></span>
                                            <span className="agent">Ben Henley (offline) <span className="datestamp">2d ago</span></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    <div className="bucket right">

                        <a href="#" className="broadcast-to-all"><i className="fa fa-bullhorn"></i> Broadcast to Everyone</a>

                        <div className="im-list-wrapper">
                            <h2>Teams</h2>
                            <ul className="im-list short">
                                <li>
                                    <a href="#">
                                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                                        <span className="agent">No "I" in Team</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <span className="no-avatar"><i className="fa fa-users"></i></span>
                                        <span className="agent">No "U" in Team</span>
                                    </a>
                                </li>
                            </ul>

                            <h2>Departments</h2>

                            <ul className="im-list short">
                                <li>
                                    <a href="#">
                                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                                        <span className="agent">Research &amp; Development &amp; Long Name</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <span className="chat-avatar" style={{"background-image": "url(./img/avatar-team.png)"}}></span>
                                        <span className="agent">Area 51</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

