import React from "react";
import { IntlMixin, FormattedMessage, FormattedNumber } from "react-intl";

import TicketsTabFilterSets from "./FiltersTab/TicketsTabFilterSets";
import TicketsTabLabels from "./LabelsTab/TicketsTabLabels";
import TicketsTabStars from "./StarsTab/TicketsTabStars";

import { connect } from 'react-redux';
import { bindActionCreators } from 'redux';
import * as TicketActions from "../Actions/FiltersActions";
import * as LabelActions from "../Actions/LabelsListActions";
import * as AppActions from "../../Application/Actions/AppActions";

import getIntlMessage from "DeskPRO/Bundle/AgentBundle/Services/Intl";

import { NavFrame, NavFrameHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

@connect(state => ({
  FilterSetsList: state.Tickets.FilterSetsList,
  FilterSetsCounts: state.Tickets.FilterSetsCounts,
  LabelsList: state.Tickets.LabelsList,
  StarsCounts: state.Tickets.StarsCounts,
  Translations: state.Translations,
  dpWindow: state.Application.dpWindow
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

  getClasses(dpWindow) {
    let classes = ['ticket-nav-frame', 'dp-nav-frame'];

    if (dpWindow.get('collapseNav')) {
      classes.push('collapsed');
    }
    if (dpWindow.get('expandedSwitcher')) {
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
      dpWindow
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
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <div part="inner">
          <NavFrameHeader icon="icon-dp-streamline-mail-2" dispatch={dispatch.bind(this)}>
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
