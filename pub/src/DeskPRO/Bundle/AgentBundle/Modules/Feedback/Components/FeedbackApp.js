import React from "react";
import AppContainer from "DeskPRO/Component/AppContainer";
import { NavContainer } from './Nav/NavContainer';
import { ListContainer } from './Nav/ListContainer';

export default class FeedbackApp extends React.Component {
    render() {
        return (
            <AppContainer thisAppId="feedback">
                <NavContainer />
                <ListContainer />
            </AppContainer>
        );
    }
}
