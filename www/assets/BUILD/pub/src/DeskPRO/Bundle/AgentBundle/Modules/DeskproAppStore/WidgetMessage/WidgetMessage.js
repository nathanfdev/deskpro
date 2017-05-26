export class WidgetMessage
{
  /**
   * @param {*} body
   * @param {String} id
   */
  constructor({ body, id })
  {
    this.props = { body, id };
  }

  get id() { return this.props.id };

  get body() { return this.props.body };
}
