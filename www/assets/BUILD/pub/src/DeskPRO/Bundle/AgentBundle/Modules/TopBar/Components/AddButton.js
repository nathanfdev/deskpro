import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import Isvg from 'react-inlinesvg';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';

class AddButton extends React.Component {
  static propTypes = {
    closeIframes: PropTypes.func,
  };
  static defaultProps = {
    closeIframes() {}
  };

  getPopupContent() {
    const items = [];
    if (window.DESKPRO_PERSON_PERMS) {
      if (window.DESKPRO_PERSON_PERMS['agent_tickets.create']) {
        items.push(<MenuItem key="ticket" onClick={this.addTicket}>
          <i className="icon mail" /> <FormattedMessage id="agent.general.ticket" />
        </MenuItem>);
      }
      if (window.DESKPRO_PERSON_PERMS['agent_people.create']) {
        items.push(<MenuItem key="person" onClick={this.addPerson}>
          <i className="icon user" /> <FormattedMessage id="agent.general.person" /></MenuItem>);
      }
      if (window.DESKPRO_PERSON_PERMS['agent_org.create']) {
        items.push(<MenuItem key="organisation" onClick={this.addOrganisation}>
          <i className="icon users" /> <FormattedMessage id="agent.general.organization" /></MenuItem>);
      }
      // if (app.getConfig('enable_twitter') && app.user.getTwitterAccountIds()|length %}
      //   items.push(<MenuItem key="twitter" onClick={this.addTweet}><i className="icon twitter" /> Tweet</MenuItem>);
      // }
      if (window.DESKPRO_PERSON_PERMS['agent_publish.create']) {
        items.push(<MenuItem key="article" onClick={this.addArticle}>
          <i className="icon edit" /> <FormattedMessage id="agent.general.article" /></MenuItem>);
        items.push(<MenuItem key="news" onClick={this.addNewsPost}>
          <i className="icon calendar outline" /> <FormattedMessage id="agent.general.news_post" /></MenuItem>);
        items.push(<MenuItem key="download" onClick={this.addDownload}>
          <i className="icon download" /> <FormattedMessage id="agent.general.download" /></MenuItem>);
        items.push(<MenuItem key="feedback" onClick={this.addFeedback}>
          <i className="icon thumbs outline up" /> <FormattedMessage id="agent.general.feedback" /></MenuItem>);
        if (window.DESKPRO_APP_SETTINGS['core.apps_guides']) {
          items.push(<MenuItem key="topic" onClick={this.addTopic}>
            <i className="icon book" /> <FormattedMessage id="agent.general.topic" /></MenuItem>);
        }
      }
      if (window.DESKPRO_APP_SETTINGS['core.apps_tasks'] && window.DESKPRO_PERSON_PERMS['agent_tasks.use']) {
        items.push(<MenuItem key="task" onClick={this.addTask}>
          <i className="icon check circle outline" /> <FormattedMessage id="agent.general.task" /></MenuItem>);
      }
    }
    if (items.length) {
      return (<div id="add-menu">
        <div className="header">Add</div>
        <div className="description">
          <div className="ui vertical menu">{items}</div>
        </div>
      </div>);
    }
    return null;
  }

  addTicket = () => {
    window.DeskPRO_Window.newTicketLoader.toggle();
    this.closePopup();
  };

  addPerson = () => {
    window.DeskPRO_Window.newPersonLoader.toggle();
    this.closePopup();
  };

  addOrganisation = () => {
    window.DeskPRO_Window.newOrganizationLoader.toggle();
    this.closePopup();
  };

  addArticle = () => {
    window.DeskPRO_Window.newArticleLoader.toggle();
    this.closePopup();
  };

  addNewsPost = () => {
    window.DeskPRO_Window.newNewsLoader.toggle();
    this.closePopup();
  };

  addDownload = () => {
    window.DeskPRO_Window.newDownloadLoader.toggle();
    this.closePopup();
  };

  addFeedback = () => {
    window.DeskPRO_Window.newFeedbackLoader.toggle();
    this.closePopup();
  };

  addTopic = () => {
    window.DeskPRO_Window.newTopicLoader.toggle();
    this.closePopup();
  };

  addTask = () => {
    window.$('form#newTaskForm input, form#newTaskForm select').val('');
    window.DeskPRO_Window.newTaskLoader.toggle();
    this.closePopup();
  };

  runCustomAddBtnClick = () => {
    window.HEADER_ADD_BTN_CLICK_ACTION();
  };

  closePopup = () => {
    this.addPopup.closePopup();
    this.props.closeIframes();
  };

  togglePopup = () => {
    this.addPopup.togglePopup();
  };

  render() {
    const content = this.getPopupContent();

    // code plugin hook to override the btn
    if (window.HEADER_ADD_BTN_CLICK_ACTION) {
      return (
        <div className="item add">
          <button className="ui button" onClick={this.runCustomAddBtnClick}>
            <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/plus.svg`} />
          </button>
        </div>
      );
    }

    if (content) {
      return (
        <div className="item add" onClick={this.togglePopup}>
          <PopUp
            positionMy="center top"
            positionAt="center bottom"
            elementId="add-menu-popup"
            zIndex={99999}
            content={content}
            ref={(c) => { this.addPopup = c; }}
            className="add_menu_popup"
            autoOpen={false}
          >
            <button className="ui button">
              <Isvg src={`${window.DESKPRO_APP_ASSETS_URL}/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/plus.svg`} />
            </button>
          </PopUp>
        </div>
      );
    }

    return null;
  }
}
export default AddButton;
