import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import Isvg from 'react-inlinesvg';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import $ from 'jquery';

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
        items.push(
          <div key="article_templates" className="sub-menu" onMouseOver={this.openSubMenu} onMouseOut={this.closeSubMenu}>
            <MenuItem key="article_templates_sub" onClick={this.openSubMenu}>
              <i className="icon copy outline" /> <FormattedMessage id="agent.content_templates.templates" />
            </MenuItem>
            <div className="sub-menu-list">
              <MenuItem key="create_from_article_template" onClick={this.createFromArticleTemplate}>
                <i className="icon edit" /> <FormattedMessage id="agent.content_templates.create_article_from_template" />
              </MenuItem>
              <MenuItem key="create_from_news_template" onClick={this.createFromNewsTemplate}>
                <i className="icon edit" /> <FormattedMessage id="agent.content_templates.create_news_from_template" />
              </MenuItem>
              <MenuItem key="manage_article_template" onClick={this.manageTemplates}>
                <i className="icon sliders horizontal" /> <FormattedMessage id="agent.content_templates.manage_templates" />
              </MenuItem>
            </div>
          </div>
        );
        items.push(<MenuItem key="news" onClick={this.addNewsPost}>
          <i className="icon calendar outline" /> <FormattedMessage id="agent.general.news_post" /></MenuItem>);
        items.push(<MenuItem key="download" onClick={this.addDownload}>
          <i className="icon download" /> <FormattedMessage id="agent.general.download" /></MenuItem>);
        items.push(<MenuItem key="community" onClick={this.addCommunityTopic}>
          <i className="icon thumbs outline up" /> <FormattedMessage id="agent.general.community" /></MenuItem>);
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

  addCommunityTopic = () => {
    window.DeskPRO_Window.newCommunityTopicLoader.toggle();
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

  createFromArticleTemplate = () => {
    const event = new CustomEvent('dpLeftDrawer', {
      detail: {
        module: 'UseContentTemplates',
        width:  745,
        type:   'article'
      }
    });

    window.document.dispatchEvent(event);
    this.closePopup();
  };

  createFromNewsTemplate = () => {
    const event = new CustomEvent('dpLeftDrawer', {
      detail: {
        module: 'UseContentTemplates',
        width:  745,
        type:   'news'
      }
    });

    window.document.dispatchEvent(event);
    this.closePopup();
  };

  manageTemplates = () => {
    const event = new CustomEvent('dpLeftDrawer', {
      detail: {
        module: 'ManageContentTemplates',
        width:  745
      }
    });

    window.document.dispatchEvent(event);
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

  openSubMenu = (event) => {
    $(event.currentTarget).closest('.sub-menu').find('> .sub-menu-list').show();
  };

  closeSubMenu = (event) => {
    $(event.currentTarget).closest('.sub-menu').find('> .sub-menu-list').hide();
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
