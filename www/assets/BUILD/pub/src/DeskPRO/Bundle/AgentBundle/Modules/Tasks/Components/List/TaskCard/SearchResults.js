import React, { PropTypes } from 'react';

export class SearchResults extends React.Component {

  static propTypes = {
    tickets: PropTypes.object,
    articles: PropTypes.object,
    chats: PropTypes.object,

    onSelect: PropTypes.func
  };

  onClick(type, value) {
    this.props.onSelect && this.props.onSelect(type, value);
  }

  render() {
    const { tickets, articles, chats } = this.props;

    return (
      <div>
        {tickets.size && [
          <h4 key="title">Tickets</h4>,
          <ul key="elements">
            {tickets.map((ticket) => {
              return (<li key={ticket.get('id')} onClick={this.onClick.bind(this, 'ticket', ticket.get('id'))}>
                {ticket.get('subject')}
              </li>);
              }
            )}
          </ul>
        ] || null}

        {articles.size && [
          <h4 key="title">Articles</h4>,
          <ul key="elements">
            {articles.map((article) =>
              <li key={article.get('id')} onClick={this.onClick.bind(this, 'article', article.get('id'))}>
                Article
              </li>
            )}
          </ul>
        ] || null}


        {chats.size && [
          <h4 key="title">Chats</h4>,
          <ul key="elements">
            {chats.map((chat) =>
              <li key={chat.get('id')} onClick={this.onClick.bind(this, 'chat', chat.get('id'))}>
                Chat
              </li>
            )}
          </ul>
        ] || null}
      </div>
    );
  }
}
