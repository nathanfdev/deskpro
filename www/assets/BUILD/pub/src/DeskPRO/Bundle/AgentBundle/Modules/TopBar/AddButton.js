import React, { PropTypes } from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { MenuItem } from 'DeskPRO/Component/Semantic/Menu';
import Isvg from 'react-inlinesvg';

class AddButton extends React.Component {
  static propTypes = {
    closeIframes: PropTypes.func
  };
  static defaultProps = {
    closeIframes() {}
  };
  constructor() {
    super();
    this.addTicket = this.addTicket.bind(this);
    this.addPerson = this.addPerson.bind(this);
    this.addOrganisation = this.addOrganisation.bind(this);
    this.addArticle = this.addArticle.bind(this);
    this.addNewsPost = this.addNewsPost.bind(this);
    this.addDownload = this.addDownload.bind(this);
    this.addFeedback = this.addFeedback.bind(this);
    this.addTask = this.addTask.bind(this);
    this.addTweet = this.addTweet.bind(this);
    this.closePopup = this.closePopup.bind(this);
    this.togglePopup = this.togglePopup.bind(this);
  }

  getPopupContent() {
    let items = [];
    if (window.DESKPRO_PERSON_PERMS) {
      if (window.DESKPRO_PERSON_PERMS['agent_tickets.create']) {
        items.push(<MenuItem key="ticket" onClick={this.addTicket}><i className="icon mail" /> Ticket</MenuItem>);
      }
      if (window.DESKPRO_PERSON_PERMS['agent_people.create']) {
        items.push(<MenuItem key="person" onClick={this.addPerson}><i className="icon user" /> Person</MenuItem>);
      }
      if (window.DESKPRO_PERSON_PERMS['agent_org.create']) {
        items.push(<MenuItem key="organisation" onClick={this.addOrganisation}><i className="icon users" /> Organisation</MenuItem>);
      }
      // if (app.getConfig('enable_twitter') && app.user.getTwitterAccountIds()|length %}
      //   items.push(<MenuItem key="twitter" onClick={this.addTweet}><i className="icon twitter" /> Tweet</MenuItem>);
      // }
      if (window.DESKPRO_PERSON_PERMS['agent_publish.create']) {
        items.push(<MenuItem key="article" onClick={this.addArticle}><i className="icon edit" /> Article</MenuItem>);
        items.push(<MenuItem key="news" onClick={this.addNewsPost}><i className="icon calendar outline" /> News post</MenuItem>);
        items.push(<MenuItem key="download" onClick={this.addDownload}><i className="icon download" /> Download</MenuItem>);
        items.push(<MenuItem key="feedback" onClick={this.addFeedback}><i className="icon thumbs outline up" /> Feedback</MenuItem>);
      }
      if (window.DESKPRO_PERSON_PERMS['agent_tasks.use']) {
        items.push(<MenuItem key="task" onClick={this.addTask}><i className="icon check circle outline" /> Task</MenuItem>);
      }
    }
    return (<div id="add-menu">
      <div className="header">Add</div>
      <div className="description">
        <div className="ui vertical menu">{items}</div>
      </div>
    </div>);
  }

  addTicket() {
    window.DeskPRO_Window.newTicketLoader.toggle();
    this.closePopup();
  }

  addPerson() {
    window.DeskPRO_Window.newPersonLoader.toggle();
    this.closePopup();
  }

  addOrganisation() {
    window.DeskPRO_Window.newOrganizationLoader.toggle();
    this.closePopup();
  }

  addArticle() {
    window.DeskPRO_Window.newArticleLoader.toggle();
    this.closePopup();
  }

  addNewsPost() {
    window.DeskPRO_Window.newNewsLoader.toggle();
    this.closePopup();
  }

  addDownload() {
    window.DeskPRO_Window.newDownloadLoader.toggle();
    this.closePopup();
  }

  addFeedback() {
    window.DeskPRO_Window.newFeedbackLoader.toggle();
    this.closePopup();
  }

  addTask() {
    window.$('form#newTaskForm input, form#newTaskForm select').val('');
    window.DeskPRO_Window.newTaskLoader.toggle();
    this.closePopup();
  }

  addTweet() {

  }

  closePopup() {
    this.refs.addPopup.closePopup();
    this.props.closeIframes();
  }

  togglePopup() {
    this.refs.addPopup.togglePopup();
  }

  render() {
    return (<div className="item add">
      <PopUp
        positionMy="center top"
        positionAt="center bottom"
        id={3}
        elementId="add-menu-popup"
        zIndex={99999}
        content={this.getPopupContent()}
        ref={'addPopup'}
        classes={['add_menu_popup']}
        autoOpen={false}
      >
        <button className="ui button" onClick={this.togglePopup}>
          <Isvg src={`${window.DESKPRO_APP_ASSETS_URL.replace(/\/$/, '')}/../src/DeskPRO/Bundle/AgentBundle/Resources/img/topbar/plus.svg`} />
        </button>
      </PopUp>
    </div>);
  }
}
export default AddButton;
