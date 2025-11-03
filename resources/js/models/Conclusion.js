export class Conclusion {
  constructor(texto = "") {
    this.texto = texto;
  }

  fromJson(data) {
    this.texto = data.texto ?? "";
  }
}
