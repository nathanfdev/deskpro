import React from "react";
import { IntlMixin, FormattedMessage, FormattedNumber } from "react-intl";

import TicketsTabFilterSets from "./FiltersTab/TicketsTabFilterSets";
import TicketsTabLabels from "./LabelsTab/TicketsTabLabels";
import TicketsTabStars from "./StarsTab/TicketsTabStars";

import { connect } from 'redux/react';
import { bindActionCreators } from 'redux';
import * as TicketActions from "../Actions/FiltersActions";
import * as LabelActions from "../Actions/LabelsListActions";
import * as AppActions from "../../Application/Actions/AppActions";

import getIntlMessage from "DeskPRO/Bundle/AgentBundle/Services/Intl";

import { NavFrame, NavFrameHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/NavFrame/index';

@connect(state => ({
  FilterSetsList: state.FilterSetsList,
  FilterSetsCounts: state.FilterSetsCounts,
  LabelsList: state.LabelsList,
  StarsCounts: state.StarsCounts,
  Translations: state.Translations,
  dp_window: state.dp_window,
}))
export default class TicketsNavContent extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showTab: "filters"
    };

    this.intl = IntlMixin;

    const { dispatch } = this.props;
    dispatch(TicketActions.loadFilterSets());
    dispatch(TicketActions.loadFilterSetsCounts());
    dispatch(LabelActions.loadLabels());
  }

  changeTab(newTab) {
    let newState = {
      ...this.state,
      showTab: newTab
    }
    this.setState(newState);
  }

  renderTab(key, title) {
    let link_class = 'show-' + key;
    let tab_class = '';
    if(this.state.showTab == key) {
      tab_class = 'active';
    }
    let clickythingy = () => this.changeTab(key);
    return (
      <li className={tab_class}>
        <a href="#" className={link_class} onClick={clickythingy}>{title}</a>
      </li>
    );
  }

  getClasses(dp_window) {
      let classes = ['ticket-nav-frame', 'dp-nav-frame'];

      if(dp_window.collapseNav) {
          classes.push('collapsed');
      }
      if(dp_window.expandedSwitcher) {
          classes.push('shifted');
      }

      return classes.join(' ');
  }

  render() {
    const {
      FilterSetsList,
      FilterSetsCounts,
      LabelsList,
      StarsCounts,
      Translations,
      dispatch,
      dp_window
    } = this.props;

    let tab = null;
    switch(this.state.showTab) {
    case "labels":
      tab = <TicketsTabLabels labelsList={LabelsList} dispatch={dispatch} />
      break;
    case "stars":
      tab = <TicketsTabStars dispatch={dispatch} starsCounts={StarsCounts} />
      break;
    case "filters":
    default:
      tab = <TicketsTabFilterSets
              filterSetsList={FilterSetsList}
              filterSetsCounts={FilterSetsCounts}
              {...bindActionCreators(TicketActions, dispatch)}
              {...this.props} />
      break;
    }

    return (
      <NavFrame dispatch={dispatch.bind(this)} dp_window={dp_window}>
        <div part="inner">
          <NavFrameHeader icon="fa-envelope-o" dispatch={dispatch.bind(this)}>
            {getIntlMessage(Translations, "foobar")}
          </NavFrameHeader>

          <ul className="tabs sidebar-tabs">
            {this.renderTab('filters', 'Filters')}
            {this.renderTab('labels', 'Labels')}
            {this.renderTab('stars', 'Stars')}
          </ul>

          {tab}
        </div>
      </NavFrame>
    );
  }
}
